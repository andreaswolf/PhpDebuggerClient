<?php
namespace AndreasWolf\DebuggerClient\Protocol\Breakpoint;


/**
 * Generic interface for a breakpoint.
 *
 * After a breakpoint has been defined, it also needs to be set in the debugger engine; until this has happened,
 * the breakpoint will have no effect.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface Breakpoint {

	const STATUS_PENDING = 0;
	const STATUS_SET = 1;

	/**
	 * Returns this breakpoint’s status.
	 *
	 * @return int
	 */
	public function getStatus();

	/**
	 * Sets the breakpoint’s status.
	 *
	 * @param $status
	 * @return int
	 */
	public function setStatus($status);

	/**
	 * Checks if the breakpoint points to the given location. This might be difficult to get, e.g. when the breakpoint
	 * is only based on a line.
	 *
	 * @param string $file
	 * @param int $line
	 * @return boolean
	 */
	public function matchesPosition($file, $line);

}
