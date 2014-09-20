<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\CommandEvent;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
		$this->transactions[$transactionId]->finish($response);
	}

	/**
	 * Runs this session initially or continues after hitting a breakpoint.
	 */
	public function run() {
		$command = new Command\Run($this);
		$this->sendCommand($command);
	}

}
