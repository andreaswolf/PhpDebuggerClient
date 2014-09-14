<?php
namespace AndreasWolf\DebuggerClient\Protocol;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\StreamDataEvent;
use AndreasWolf\DebuggerClient\Streams\StreamDataSink;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * The connecting piece between the stream and the debugging session. Receives data from a stream and
 * hands the parsed data to the session.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebuggerEngineMessageParser implements StreamDataSink {

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

		$attributes = $xmlElement->attributes();
		if ($xmlElement->getName() == 'init') {
			$this->session->initialize($attributes['idekey'], $attributes['appid'], $attributes['fileuri']);
		} elseif ($xmlElement->getName() == 'response') {
			$transactionId = $attributes['transaction_id'];
			$this->session->finishTransaction($transactionId, $xmlElement);
		}
	}

}
