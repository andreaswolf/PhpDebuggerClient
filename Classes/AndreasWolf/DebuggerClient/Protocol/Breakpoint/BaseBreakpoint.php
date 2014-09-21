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

}
