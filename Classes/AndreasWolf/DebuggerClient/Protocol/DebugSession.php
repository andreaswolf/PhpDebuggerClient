<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\CommandEvent;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Protocol\Response\EngineStatusResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * A debugging session. It is opened when a debugger engine connects and closed when it disconnects again.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSession {

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
	// TODO this status is currently never set
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


	public function __construct() {
		$this->eventDispatcher = Bootstrap::getInstance()->getEventDispatcher();
	}

	public function setMessageParser(DebuggerEngineMessageParser $messageParser) {
		$this->messageParser = $messageParser;
	}

	public function setCommandProcessor(DebugSessionCommandProcessor $processor) {
		$this->commandProcessor = $processor;
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
		$oldStatus = $this->status;
		switch ($engineResponse->getStatus()) {
			case EngineStatusResponse::STATUS_RUNNING:
				$this->status = self::STATUS_RUNNING;
				break;

			case EngineStatusResponse::STATUS_BREAK:
				$this->status = self::STATUS_PAUSED;
				break;

			case EngineStatusResponse::STATUS_STOPPING:
				$this->status = self::STATUS_STOPPED;
				// TODO trigger event
				break;
		}

		if ($oldStatus != $this->status) {
			$this->eventDispatcher->dispatch('session.status.changed', new SessionEvent($this));
		}
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

		$this->run();
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

}
