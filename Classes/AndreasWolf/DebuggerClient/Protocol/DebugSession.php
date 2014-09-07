<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
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
	 * The commands that were sent to the server
	 *
	 * @var array
	 */
	protected $commandsSent = array();

	/**
	 * @var DebuggerEngineMessageParser
	 */
	protected $messageParser;

	/**
	 * @var DebugSessionCommandProcessor
	 */
	protected $commandProcessor;


	public function __construct() {
		$this->messageParser = new DebuggerEngineMessageParser($this);
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

		Bootstrap::getInstance()->getEventDispatcher()->dispatch('session.initialized', new SessionEvent($this));

		$this->run();
	}

	/**
	 * Sends the given command to the debugger engine.
	 *
	 * @param DebuggerCommand $command
	 */
	public function sendCommand(DebuggerCommand $command) {
		$this->commandProcessor->send($command);
	}

	/**
	 * Runs this session initially or continues after hitting a breakpoint.
	 */
	public function run() {
		$command = new Command\Run();
		$this->sendCommand($command);
	}

}
