<?php
namespace AndreasWolf\DebuggerClient\Event;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use Symfony\Component\EventDispatcher\Event;


/**
 * Event related to a debugging session
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class SessionEvent extends Event {

	protected $session;

	public function __construct(DebugSession $session) {
		$this->session = $session;
	}

	/**
	 * @return \AndreasWolf\DebuggerClient\Session\DebugSession
	 */
	public function getSession() {
		return $this->session;
	}

}
