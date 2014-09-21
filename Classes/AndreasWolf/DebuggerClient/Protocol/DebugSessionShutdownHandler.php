<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Handler for closing the stream when a session is shut down.
 *
 * When a program has finished running, the debugger engine keeps the connection open to give the IDE (the client)
 * a chance to collect some statistics or do cleanup. The debugging session ends when the client closes the stream.
 *
 * This process is done via this class because the session should not know about its stream (to keep the abstraction
 * of stream-related stuff intact).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionShutdownHandler implements EventSubscriberInterface {

	/**
	 * @var DebugSession
	 */
	protected $session;

	/**
	 * @var StreamWrapper
	 */
	protected $stream;


	public function __construct(DebugSession $session, StreamWrapper $stream) {
		$this->session = $session;
		$this->stream = $stream;
	}

	/**
	 * @param SessionEvent $event
	 */
	public function sessionStatusChangeHandler(SessionEvent $event) {
		if ($event->getSession() != $this->session) {
			return;
		}

		if ($this->session->getStatus() == DebugSession::STATUS_STOPPED) {
			$this->stream->shutdown();
		}
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'session.status.changed' => 'sessionStatusChangeHandler',
		);
	}

}
