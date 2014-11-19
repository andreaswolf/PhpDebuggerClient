<?php
namespace AndreasWolf\DebuggerClient\Session;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\CommandEvent;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\BreakpointCollection;
use AndreasWolf\DebuggerClient\Protocol\Command;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;
use AndreasWolf\DebuggerClient\Protocol\DebuggerEngineMessageParser;
use AndreasWolf\DebuggerClient\Protocol\DebugSessionCommandProcessor;
use AndreasWolf\DebuggerClient\Protocol\Response\EngineStatusResponse;
use AndreasWolf\DebuggerClient\Session\Transaction;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * A debugging session. It is opened when a debugger engine connects and closed when it disconnects again.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSession implements EventSubscriberInterface {

	protected $initialized = FALSE;

	/**
	 * @var string
	 */
	protected $ideKey;

	/**
	 * @var string
	 */
	protected $applicationId;

	/**
	 * The first file the debugger engine opened.
	 *
	 * @var string
	 */
	protected $startFile;

	/**
	 * @var int
	 */
	protected $transactionCounter = 0;

	/**
	 * @var Transaction[]
	 */
	protected $transactions = array();

	const STATUS_NEW = 0;
	// TODO this status is currently never set
	const STATUS_INITIALIZED = 1;
	/** The program is being executed */
	const STATUS_RUNNING = 2;
	/** Execution is interrupted, e.g. when a breakpoint was hit */
	const STATUS_PAUSED = 3;
	/** Program execution has ended; this is the point to e.g. collect statistics */
	const STATUS_STOPPED = 4;
	/** The session was closed, no further interaction with the debugger possible */
	const STATUS_CLOSED = 5;

	/**
	 * The current status of this session; see STATUS_* constants for possible values.
	 *
	 * This does not reflect the debugger engine status 1:1, as
	 *
	 * @var int
	 */
	protected $status = self::STATUS_NEW;

	/**
	 * The last location the program execution is known to have been at.
	 *
	 * This is an array consisting of the file name and the line within that file.
	 * If the status is "running", this will most likely not reflect the current position; just as well it is completely
	 * unusable if the session has already ended.
	 *
	 * @var array[string, int]
	 */
	protected $lastKnownPosition;

	/**
	 * @var DebuggerEngineMessageParser
	 */
	protected $messageParser;

	/**
	 * @var DebugSessionCommandProcessor
	 */
	protected $commandProcessor;

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var BreakpointCollection
	 */
	protected $breakpoints;

	protected $autorun = TRUE;


	public function __construct() {
		$this->eventDispatcher = Bootstrap::getInstance()->getEventDispatcher();
		$this->eventDispatcher->addSubscriber($this);
	}

	/**
	 * @param EventDispatcherInterface $eventDispatcher
	 */
	public function setEventDispatcher($eventDispatcher) {
		$this->eventDispatcher = $eventDispatcher;
	}

	public function setMessageParser(DebuggerEngineMessageParser $messageParser) {
		$this->messageParser = $messageParser;
	}

	public function setCommandProcessor(DebugSessionCommandProcessor $processor) {
		$this->commandProcessor = $processor;
	}

	/**
	 * @param BreakpointCollection $breakpointCollection
	 */
	public function setBreakpointCollection(BreakpointCollection $breakpointCollection) {
		$this->breakpoints = $breakpointCollection;
		$this->eventDispatcher->addSubscriber($this->breakpoints);
	}

	/**
	 * @return BreakpointCollection
	 */
	public function getBreakpointCollection() {
		return $this->breakpoints;
	}

	/**
	 * Disables autorun for this session.
	 *
	 * If you do this, you have to call run() yourself when session initialization is finished.
	 *
	 * @return void
	 */
	public function disableAutorun() {
		$this->autorun = FALSE;
	}

	/**
	 * @return bool
	 */
	public function isAutorunEnabled() {
		return $this->autorun === TRUE;
	}

	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->initialized;
	}

	/**
	 * @return \AndreasWolf\DebuggerClient\Protocol\DebuggerEngineMessageParser
	 */
	public function getMessageParser() {
		return $this->messageParser;
	}

	/**
	 * Returns the current session status.
	 *
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Sets the status from a debugger engine response
	 *
	 * @param EngineStatusResponse $engineResponse
	 */
	public function setStatusFromDebuggerEngine(EngineStatusResponse $engineResponse) {
		$status = NULL;
		switch ($engineResponse->getStatus()) {
			case EngineStatusResponse::STATUS_RUNNING:
				$status = self::STATUS_RUNNING;
				break;

			case EngineStatusResponse::STATUS_BREAK:
				$status = self::STATUS_PAUSED;
				break;

			case EngineStatusResponse::STATUS_STOPPING:
				$status = self::STATUS_STOPPED;
				// TODO trigger event
				break;
		}

		if ($status !== NULL) {
			$this->updateStatus($status);
		}
		if ($engineResponse->hasFilename()) {
			$this->lastKnownPosition = array($engineResponse->getFilename(), $engineResponse->getLineNumber());
			$this->eventDispatcher->dispatch('session.file-position.updated', new SessionEvent($this));
		}
	}

	/**
	 * Updates this session’s status.
	 *
	 * Also triggers a change-event if the status has changed (i.e. it doesn’t harm to set the same status again)
	 *
	 * @param int $newStatus
	 */
	protected function updateStatus($newStatus) {
		if ($newStatus == $this->status) {
			return;
		}
		$this->status = $newStatus;
		$this->eventDispatcher->dispatch('session.status.changed', new SessionEvent($this));

		if ($this->status == self::STATUS_STOPPED) {
			// properly close session after it was stopped; using two status gives the possibility to do proper cleanup,
			// collect data etc. before the session and stream are finally discarded.
			$this->updateStatus(self::STATUS_CLOSED);
		}
	}

	public function close() {
		$this->eventDispatcher->removeSubscriber($this->breakpoints);
		$this->status = self::STATUS_CLOSED;
	}

	/**
	 * Returns the last position the program execution was known to have been at.
	 *
	 * This does not necessarily reflect the current position; see the description of property `lastKnownPosition` for
	 * more information.
	 *
	 * @return array(string, int)
	 */
	public function getLastKnownFilePosition() {
		return $this->lastKnownPosition;
	}

	/**
	 * @param string $ideKey
	 * @param string $applicationId
	 * @param string $startFile
	 */
	public function initialize($ideKey, $applicationId, $startFile) {
		$this->ideKey = $ideKey;
		$this->applicationId = $applicationId;
		$this->startFile = $startFile;
		$this->initialized = TRUE;

		$this->eventDispatcher->dispatch('session.initialized', new SessionEvent($this));
	}

	/**
	 * Event handler run after the session has been initialized ()
	 *
	 * @param SessionEvent $event
	 * @return void
	 */
	public function sessionInitializedHandler(SessionEvent $event) {
		if ($this->autorun === TRUE) {
			$this->run();
		}
	}

	/**
	 * @param DebuggerCommand $command
	 */
	public function sendCommand(DebuggerCommand $command) {
		$this->commandProcessor->send($command);

		$this->eventDispatcher->dispatch('command.sent', new CommandEvent($command, $this));
	}

	/**
	 * @param DebuggerCommand $command
	 * @return Transaction
	 */
	public function startTransaction(DebuggerCommand $command) {
		$transactionId = count($this->transactions);
		$transaction = new Transaction($this, $transactionId, $command);

		$this->transactions[$transactionId] = $transaction;

		return $transaction;
	}

	/**
	 * @param int $transactionId
	 * @param \SimpleXMLElement $response
	 */
	public function finishTransaction($transactionId, \SimpleXMLElement $response) {
		$transaction = $this->transactions[$transactionId];
		$command = $transaction->getCommand();

		$transaction->finish($response);

		$this->eventDispatcher->dispatch('command.response.processed', new CommandEvent($command, $this));
	}

	/**
	 * Runs this session initially or continues after hitting a breakpoint.
	 */
	public function run() {
		$command = new Command\Run($this);
		$this->sendCommand($command);
		$this->status = self::STATUS_RUNNING;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'session.initialized' => array('sessionInitializedHandler', -1000)
		);
	}


}
