<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\StreamDataEvent;
use AndreasWolf\DebuggerClient\Streams\StreamDataSink;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Parser for debugger engine messages. Receives the data from the
 *
 * @author Andreas Wolf <FIXME>
 */
class DebuggerEngineMessageParser implements StreamDataSink, EventSubscriberInterface {

	/**
	 * @var DebugSession
	 */
	protected $session;

	/**
	 * @var string
	 */
	protected $identifier;

	public function __construct(DebugSession $session) {
		$this->session = $session;
		$this->identifier = uniqid();

		Bootstrap::getInstance()->getEventDispatcher()->addSubscriber($this);
	}

	/**
	 * Returns a unique identifier for this sink, to make it possible to address data packets from this sink's stream.
	 *
	 * @return string
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * Processes data received from the stream
	 */
	public function processMessage($receivedData) {
		$xmlElement = simplexml_load_string($receivedData);
		if ($xmlElement === FALSE) {
			throw new \InvalidArgumentException('Could not decode message contents: ' . $receivedData);
		}

		if ($xmlElement->getName() == 'init') {
			$attributes = $xmlElement->attributes();
			$this->session->initialize($attributes['idekey'], $attributes['appid'], $attributes['fileuri']);
		} elseif ($xmlElement->getName() == 'response') {
			// TODO implement
		}
	}

	/**
	 * @param StreamDataEvent $e
	 */
	public function receivedDataEvent(StreamDataEvent $e) {
		$this->processMessage($e->getData());
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'stream.data.received' => 'receivedDataEvent'
		);
	}

}
