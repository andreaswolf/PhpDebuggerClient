<?php
namespace AndreasWolf\DebuggerClient\Event;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint;
use Symfony\Component\EventDispatcher\Event;


/**
 * An event related to a breakpoint.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointEvent extends Event {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	public function __construct(Breakpoint $breakpoint) {
		$this->breakpoint = $breakpoint;
	}

	/**
	 * @return Breakpoint
	 */
	public function getBreakpoint() {
		return $this->breakpoint;
	}

}
