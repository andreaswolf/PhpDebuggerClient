<?php
namespace AndreasWolf\DebuggerClient\Protocol;
/**
 * Base class for debugger engine commands.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class DebuggerBaseCommand implements DebuggerCommand {

	/**
	 * @var int
	 */
	protected $status = self::STATUS_NEW;

	/**
	 * @var callable
	 */
	protected $responseCallback;

	/**
	 * @var DebuggerCommandResult
	 */
	protected $response;

	/**
	 * @var DebugSession
	 */
	protected $session;

	public function __construct(DebugSession $session) {
		$this->session = $session;
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Called by the command processor when the command was sent.
	 *
	 * As this is a 1:1 interaction and the processor knows the command interface, we don’t need to use an event for
	 * this.
	 *
	 * @return void
	 */
	public function onSend() {
	}

	/**
	 * Sets a callback to call when a response is processed.
	 *
	 * This is not implemented with an event because we might have a huge number of commands with different callbacks,
	 * so using a global event might lead to a lot of unnecessary method calls.
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function onResponseProcessed($callback) {
		if (is_callable($callback)) {
			$this->responseCallback = $callback;
		}
	}

}
