<?php
namespace AndreasWolf\DebuggerClient\Streams;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\StreamDataEvent;


/**
 * A stream wrapper for the connection to the debugger engine.
 *
 * This also handles the incoming data, i.e. makes sure that all messages are dispatched to the session’s message
 * parser.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebuggerEngineStream extends StreamWrapper implements StreamDataHandler {

	/**
	 * The sink this stream should deliver data to.
	 *
	 * @var StreamDataSink
	 */
	protected $sink;

	/**
	 * @param resource $stream
	 */
	public function __construct($stream) {
		parent::__construct($stream);

		$this->dataHandler = $this;
	}

	public function setSink(StreamDataSink $sink) {
		$this->sink = $sink;
	}

	public function handleIncomingData() {
		$packets = $this->readData();

		if (count($packets) == 0) {
			// no data received; could be because the stream was closed
			return;
		}

		foreach ($packets as $packet) {
			$dataEvent = new StreamDataEvent($this, $packet);
			Bootstrap::getInstance()->getEventDispatcher()->dispatch('stream.data.received', $dataEvent);

			if (is_object($this->sink)) {
				$this->sink->processMessage($packet);
			}
		}
	}

	/**
	 * Reads data from the stream.
	 *
	 * Note that this function will block if a packet has not been completely sent by the debugger.
	 *
	 * @return string[]
	 */
	protected function readData() {
		$bytesToRead = 1500;
		$data = $beginningOfNextPacket = '';
		$nextPacketLength = 0;

		$packets = $fragments = array();
		$inPacket = FALSE;

		// FIXME this does not store remaining content for the next run; it might therefore get lost.
		while ($readBytes = stream_socket_recvfrom($this->stream, $bytesToRead)) {
			$moreData = strlen($readBytes) == $bytesToRead;

			$data .= $readBytes;
			$fragments = explode("\0", $readBytes);

			foreach ($fragments as $fragment) {
				if (strlen(trim($fragment)) == '') {
					// empty fragments happen e.g. at the end of a data packet, with the trailing zero byte
					continue;
				}

				if ($inPacket === TRUE) {
					if (strlen($fragment) === $nextPacketLength) {
						// we received a full package
						$packets[] = $fragment;
						$beginningOfNextPacket = '';
						$nextPacketLength = 0;
						$inPacket = FALSE;
					} else {
						// only partial package
						if ($beginningOfNextPacket == '') {
							$beginningOfNextPacket = $fragment;
						} else {
							$beginningOfNextPacket .= $fragment;

							if (strlen($beginningOfNextPacket) == $nextPacketLength) {
								$packets[] = $beginningOfNextPacket;
								$beginningOfNextPacket = '';
								$nextPacketLength = 0;
								$inPacket = FALSE;
							} elseif (strlen($beginningOfNextPacket) > $nextPacketLength) {
								// Error, we received too much data for one package
								throw new \RuntimeException('Too much data: ' . strlen($beginningOfNextPacket) . ' vs ' . $nextPacketLength . " - " . $beginningOfNextPacket);
							}
						}
					}
				} else {
					if (is_numeric($fragment)) {
						$nextPacketLength = (int)$fragment;
						$inPacket = TRUE;
					} else {
						// This should not happen…
						throw new \RuntimeException('Oops. No segment length');
					}
				}
			}
			if (!$moreData) {
				break;
			}
		}

		return $packets;
	}

}
 