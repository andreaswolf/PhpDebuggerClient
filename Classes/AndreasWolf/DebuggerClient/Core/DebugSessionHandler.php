<?php
namespace AndreasWolf\DebuggerClient\Core;

use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Event\StreamEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\BreakpointCollection;
use AndreasWolf\DebuggerClient\Protocol\DebuggerEngineMessageParser;
use AndreasWolf\DebuggerClient\Protocol\DebugSession;
use AndreasWolf\DebuggerClient\Protocol\DebugSessionCommandProcessor;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Handler for debugging sessions. This reacts to a new debugger connection being opened by a debugger engine and
 * creates a new session for it. Further handling of the session is then delegated to the session object.
 *
 * TODO probably rename this to DebugSessionFactory and move it to DebuggerProtocol
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionHandler implements EventSubscriberInterface {

	/**
	 * @var DebugSession
	 */
	protected $currentSession;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	protected $eventDispatcher;

	/**
	 * @var DebuggerEngineMessageParser
	 */
	protected $messageParser;

	public function __construct() {
		// TODO find a better way to do this…
		$this->eventDispatcher = Bootstrap::getInstance()->getEventDispatcher();
		$this->eventDispatcher->addSubscriber($this);
	}

	/**
	 * @param StreamEvent $e
	 * @throws \RuntimeException If there is an existing session
	 */
	public function newConnectionEvent(StreamEvent $e) {
		if ($this->currentSession !== NULL) {
			throw new \RuntimeException('Only one session supported currently :-(');
		}
		$this->createDebugSession($e->getStreamWrapper());
		echo "Created new session\n";

		$event = new SessionEvent($this->currentSession);
		$this->eventDispatcher->dispatch('session.opened', $event);
	}

	/**
	 * Creates a session object
	 *
	 * @param DebuggerEngineStream $debuggerStream
	 */
	protected function createDebugSession(DebuggerEngineStream $debuggerStream) {
		$this->currentSession = new DebugSession();

		$messageParser = new DebuggerEngineMessageParser($this->currentSession);
		// wire the message parser to the session and the stream
		$this->currentSession->setMessageParser($messageParser);
		$debuggerStream->setSink($messageParser);

		$this->currentSession->setBreakpointCollection(new BreakpointCollection($this->currentSession));
		$this->currentSession->setCommandProcessor(new DebugSessionCommandProcessor($this->currentSession, $debuggerStream));
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
