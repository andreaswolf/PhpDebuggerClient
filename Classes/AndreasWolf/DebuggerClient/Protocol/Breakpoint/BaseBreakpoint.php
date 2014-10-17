<?php
namespace AndreasWolf\DebuggerClient\Protocol\Breakpoint;


/**
 * Abstract implementation of a breakpoint containing the status-related methods that are common for all breakpoint
 * implementations.
 *
 * @author Andreas Wolf <aw@foundata>
 */
abstract class BaseBreakpoint implements Breakpoint {

	/**
	 * @var int
	 */
	protected $status = self::STATUS_PENDING;

	/**
	 * @var callable
	 */
	protected $hitCallback;

	/**
	 * Returns this breakpoint’s status.
	 *
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Sets the breakpoint’s status.
	 *
	 * @param $status
	 * @return int
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * Should be called when this breakpoint was hit.
	 *
	 * @return void
	 */
	public function hit() {
		if (is_callable($this->hitCallback)) {
			call_user_func($this->hitCallback);
		}
	}

	/**
	 * Sets a callback that is triggered when the breakpoint was hit.
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function onHit(callable $callback) {
		$this->hitCallback = $callback;
	}

}
