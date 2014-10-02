<?php
namespace AndreasWolf\DebuggerClient\Event;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use Symfony\Component\EventDispatcher\Event;


/**
 * An event related to a breakpoint.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointEvent extends SessionEvent {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	public function __construct(Breakpoint $breakpoint, DebugSession $session) {
		$this->breakpoint = $breakpoint;
		parent::__construct($session);
	}

	/**
	 * @return Breakpoint
	 */
	public function getBreakpoint() {
		return $this->breakpoint;
	}

}
