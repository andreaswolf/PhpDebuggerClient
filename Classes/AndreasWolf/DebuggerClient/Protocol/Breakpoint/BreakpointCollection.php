<?php
namespace AndreasWolf\DebuggerClient\Protocol\Breakpoint;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event;
use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * A collection of breakpoints associated with a session.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointCollection implements EventSubscriberInterface {

	/**
	 * @var DebugSession
	 */
	protected $session;

	/**
	 * @var Breakpoint[]
	 */
	protected $breakpoints = array();


	/**
	 * @param DebugSession $session
	 */
	public function __construct(DebugSession $session) {
		$this->session = $session;
	}

	/**
	 * Registers a new breakpoint.
	 *
	 * @param Breakpoint $breakpoint
	 */
	public function add(Breakpoint $breakpoint) {
		$this->breakpoints[] = $breakpoint;
	}

	/**
	 * Triggered when the current position of program execution was updated.
	 *
	 * @param Event\SessionEvent $event
	 */
	public function filePositionUpdateHandler(Event\SessionEvent $event) {
		$session = $event->getSession();

		if ($session->getStatus() === DebugSession::STATUS_PAUSED) {
			// we’ve hit a breakpoint
			$currentPosition = $session->getLastKnownFilePosition();
			foreach ($this->breakpoints as $breakpoint) {
				if ($breakpoint->matchesPosition($currentPosition[0], $currentPosition[1])) {
					Bootstrap::getInstance()->getEventDispatcher()->dispatch(
						'session.breakpoint.hit', new Event\BreakpointEvent($breakpoint)
					);

					break;
				}
			}
		}
	}

	/**
	 * Triggered when the session has been initialized (= we have received the first message from the debugger). Takes
	 * care of setting all breakpoints then.
	 */
	public function sessionInitializedHandler() {
		foreach ($this->breakpoints as $breakpoint) {
			$command = new BreakpointSet($this->session, $breakpoint);
			$this->session->sendCommand($command);
		}
	}

	/**
	 * Triggered when a breakpoint has been set.
	 *
	 * @param Event\BreakpointEvent $event
	 */
	public function breakpointSetHandler(Event\BreakpointEvent $event) {
		$breakpoint = $event->getBreakpoint();
		// TODO check if breakpoint belongs to this session

		$breakpoint->setStatus(Breakpoint::STATUS_SET);
	}

	/**
	 * Triggered when a breakpoint was hit
	 *
	 * @param Event\BreakpointEvent $event
	 */
	public function breakpointHitHandler(Event\BreakpointEvent $event) {
		// continue execution; this handler is invoked with a very low priority, so everything else should be done by
		// now
		$this->session->run();
	}


	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'session.initialized' => 'sessionInitializedHandler',
			'session.file-position.updated' => 'filePositionUpdateHandler',
			'session.breakpoint.set' => 'breakpointSetHandler',
			'session.breakpoint.hit' => array(array('breakpointHitHandler', -1000)),
		);
	}

}
