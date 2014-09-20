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
	 * @var DebugSession
	 */
	protected $session;

	public function __construct(DebugSession $session, DebuggerEngineStream $debuggerStream) {
		$this->stream = $debuggerStream;
		$this->session = $session;
	}

	/**
	 * Send a command to the debugger engine.
	 *
	 * Don't call this directly, use ``DebugSession::sendCommand()`` instead.
	 *
	 * @param DebuggerCommand $command
	 * @return void
	 */
	public function send(DebuggerCommand $command) {
		$transaction = $this->session->startTransaction($command);

		$commandString = sprintf('%s -i %d %s',
			$command->getNameForProtocol(), $transaction->getId(), $command->getArgumentsAsString()
		);
		// having a trailing space at the end leads to a parse error in Xdebug
		$commandString = trim($commandString) . "\0";
		$this->stream->sendData($commandString);
	}

}
