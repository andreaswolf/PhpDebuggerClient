<?php
namespace AndreasWolf\DebuggerClient\Core;

use AndreasWolf\DebuggerClient\Event\StreamEvent;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Handler for debugging sessions. This reacts to a new debugger connection being opened by a debugger engine and
 * creates a new session for it. Further handling of the session is then delegated to the session object.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionHandler implements EventSubscriberInterface {

	public function __construct() {
		// TODO find a better way to do this…
		Bootstrap::getInstance()->getEventDispatcher()->addSubscriber($this);
	}

	/**
	 * @param StreamEvent $e
	 * @throws \RuntimeException If there is an existing session
	 */
	public function newConnectionEvent(StreamEvent $e) {
		echo "Created new session\n";
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents() {
		return array('stream.connection.opened' => 'newConnectionEvent');
	}

}
