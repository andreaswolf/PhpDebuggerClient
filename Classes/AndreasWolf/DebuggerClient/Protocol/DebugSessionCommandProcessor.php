<?php
namespace AndreasWolf\DebuggerClient\Protocol;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;


/**
 * Processor for commands sent to the debugger engine. Takes care of serializing them into a usable format and sending
 * them through the output stream.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionCommandProcessor {

	/**
	 * @var \AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream
	 */
	protected $stream;

	/**
	 * A list of commands that was already sent to the server
	 *
	 * @var array
	 */
	protected $commandsSent = array();

	public function __construct(DebuggerEngineStream $debuggerStream) {
		$this->stream = $debuggerStream;
	}

	/**
	 * Send a command to the debugger engine.
	 *
	 * @param DebuggerCommand $command
	 * @return void
	 */
	public function send(DebuggerCommand $command) {
		$commandString = sprintf('%s -i %d %s',
			$command->getNameForProtocol(), count($this->commandsSent), $command->getArgumentsAsString()
		);
		// having a trailing space at the end leads to a parse error in Xdebug
		$commandString = trim($commandString) . "\0";
		$this->stream->sendData($commandString);

		$this->commandsSent[] = $command;
	}

}
