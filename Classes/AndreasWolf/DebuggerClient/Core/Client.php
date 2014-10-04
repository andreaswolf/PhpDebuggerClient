<?php
namespace AndreasWolf\DebuggerClient\Core;
use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Core\DebugSessionHandler;
use AndreasWolf\DebuggerClient\Event\StreamEvent;
use AndreasWolf\DebuggerClient\Streams\StreamWatcher;
use AndreasWolf\DebuggerClient\Proxy\ProxyListener;
use AndreasWolf\DebuggerClient\Streams\ConnectionListener;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Proxy for debugger connections. This will sit and listen for incoming debugger connections,
 * connect to the IDE and send data back and forth between them.
 *
 * TODO make the addresses and ports configurable
 */
class Client implements EventDispatcherInterface {

	/**
	 * @var StreamWatcher
	 */
	protected $streamWatcher;

	/**
	 * @var bool
	 */
	protected $debug = FALSE;

	/**
	 * The port we should listen on for debugger connections
	 *
	 * @var int
	 */
	protected $debuggerPort = 9000;

	/**
	 * The address we should listen on for debugger connections.
	 *
	 * @var string
	 */
	protected $debuggerListenAddress = '0.0.0.0';

	/**
	 * The stream used to listen for incoming debugger connections
	 *
	 * @var StreamWrapper
	 */
	protected $debuggerListenStream;

	/**
	 * @var string
	 */
	protected $streamUriTemplate = 'tcp://%s:%s';

	/**
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;


	public function __construct() {
		$this->eventDispatcher = Bootstrap::getInstance()->getEventDispatcher();
	}

	/**
	 * Runs the debugging session
	 */
	public function run() {
		$this->streamWatcher = new StreamWatcher();
		$this->setUpListenerStream();
		$this->streamWatcher->attachStream($this->debuggerListenStream);

		$this->eventDispatcher->addListener('stream.connection.opened', function(StreamEvent $event) {
			// attach a new stream to the watcher as soon as it is opened; otherwise incoming data would
			// never be received by the application
			$this->streamWatcher->attachStream($event->getStreamWrapper());
		// high priority because we want to be sure that the stream is instantly watched
		}, 1000);

		$debugSessionHandler = new DebugSessionHandler();

		while (true) {
			$this->debug("Watching streams for data");
			$this->streamWatcher->watchAndNotifyActiveStreams();
		}
	}

	protected function debug($data) {
		if ($this->debug === FALSE) {
			return;
		}
		$lines = explode("\n", $data);
		foreach ($lines as $line) {
			echo "[DEBUG] ", $line, "\n";
		}
	}

	/**
	 * Creates the debugger listener stream.
	 */
	protected function setUpListenerStream() {
		$this->debuggerListenStream = new StreamWrapper(stream_socket_server(
			sprintf($this->streamUriTemplate, $this->debuggerListenAddress, $this->debuggerPort), $errno, $errstr
		));
		$this->debuggerListenStream->setDataHandler(new ConnectionListener($this->debuggerListenStream));

		$this->eventDispatcher->dispatch('listener.ready', new StreamEvent($this->debuggerListenStream));

		$this->debug("Set up streams");
	}

	public function enableDebugOutput() {
		$this->debug = TRUE;
	}

	public function disableDebugOutput() {
		$this->debug = FALSE;
	}

	/**
	 * Adds a new stream that should be watched in this classes run loop.
	 *
	 * @param StreamWrapper $stream
	 */
	public function attachStream(StreamWrapper $stream) {
		$this->streamWatcher->attachStream($stream);
	}

	/********************************************
	 * Event dispatcher
	 ********************************************/

	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param string $eventName The name of the event to dispatch. The name of
	 *                          the event is the name of the method that is
	 *                          invoked on listeners.
	 * @param Event $event The event to pass to the event handlers/listeners.
	 *                          If not supplied, an empty Event instance is created.
	 *
	 * @return Event
	 *
	 * @api
	 */
	public function dispatch($eventName, Event $event = NULL) {
		$this->debug('Dispatching event ' . $eventName);
		return $this->eventDispatcher->dispatch($eventName, $event);
	}

	/**
	 * Adds an event listener that listens on the specified events.
	 *
	 * @param string $eventName The event to listen on
	 * @param callable $listener The listener
	 * @param int $priority The higher this value, the earlier an event
	 *                            listener will be triggered in the chain (defaults to 0)
	 *
	 * @api
	 */
	public function addListener($eventName, $listener, $priority = 0) {
		$this->eventDispatcher->addListener($eventName, $listener, $priority);
	}

	/**
	 * Adds an event subscriber.
	 *
	 * The subscriber is asked for all the events he is
	 * interested in and added as a listener for these events.
	 *
	 * @param EventSubscriberInterface $subscriber The subscriber.
	 *
	 * @api
	 */
	public function addSubscriber(EventSubscriberInterface $subscriber) {
		$this->eventDispatcher->addSubscriber($subscriber);
	}

	/**
	 * Removes an event listener from the specified events.
	 *
	 * @param string|array $eventName The event(s) to remove a listener from
	 * @param callable $listener The listener to remove
	 */
	public function removeListener($eventName, $listener) {
		$this->eventDispatcher->removeListener($eventName, $listener);
	}

	/**
	 * Removes an event subscriber.
	 *
	 * @param EventSubscriberInterface $subscriber The subscriber
	 */
	public function removeSubscriber(EventSubscriberInterface $subscriber) {
		$this->eventDispatcher->removeSubscriber($subscriber);
	}

	/**
	 * Gets the listeners of a specific event or all listeners.
	 *
	 * @param string $eventName The name of the event
	 *
	 * @return array The event listeners for the specified event, or all event listeners by event name
	 */
	public function getListeners($eventName = NULL) {
		return $this->eventDispatcher->getListeners($eventName);
	}

	/**
	 * Checks whether an event has any registered listeners.
	 *
	 * @param string $eventName The name of the event
	 *
	 * @return bool    true if the specified event has any listeners, false otherwise
	 */
	public function hasListeners($eventName = NULL) {
		return $this->eventDispatcher->hasListeners($eventName);
	}

}
