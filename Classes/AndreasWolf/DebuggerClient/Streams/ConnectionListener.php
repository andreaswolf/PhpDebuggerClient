<?php
namespace AndreasWolf\DebuggerClient\Streams;
use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\StreamEvent;


/**
 * Wraps a listening stream that reacts to new connections.
 *
 * When a new connection is encountered, it is accepted, a stream wrapper is created and other components are notified.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ConnectionListener implements StreamDataHandler {

	/**
	 * @var StreamWrapper
	 */
	protected $wrapper;

	public function __construct(StreamWrapper $wrapper) {
		$this->wrapper = $wrapper;
	}

	/**
	 * Handles an incoming connection by accepting it and wrapping the new stream appropriately.
	 *
	 * To get notified for new events, listen to the "stream.opened" event.
	 */
	public function handleIncomingData() {
		$dataStream = stream_socket_accept($this->wrapper->getStream());
		$streamWrapper = new StreamWrapper($dataStream);

		$event = new StreamEvent($streamWrapper);
		Bootstrap::getInstance()->getEventDispatcher()->dispatch('stream.connection.opened', $event);
	}

}
