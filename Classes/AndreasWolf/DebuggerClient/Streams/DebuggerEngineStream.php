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
		$data = $this->readData();

		if (is_object($this->sink)) {
			$this->sink->processMessage($data);
		}
	}

	/**
	 * Reads data from the stream.
	 *
	 * Note that this function will block if a packet has not been completely sent by the debugger.
	 *
	 * @return string
	 */
	protected function readData() {
		$bytesToRead = 8192;
		$data = '';
		$packetLength = $dataLength = 0;

		// TODO this currently cannot cope with multiple packets received at once
		while (!feof($this->stream) || ($packetLength > 0 && strlen($data) < $packetLength)) {
			$data .= fread($this->stream, $bytesToRead);

			// the length of this data packet is written directly at the beginning of the data, separated by \0
			if ($packetLength === 0) {
				$dataLength = (int)substr($data, 0, strpos($data, "\0"));
				$packetLength = $dataLength + 1 /* the zerobyte separator */ + strlen((string)$dataLength);
				$data = substr($data, strpos($data, "\0") + 1);
			}

			if (strlen($data) >= $dataLength) {
				// cut off trailing zero bytes etc.
				$data = substr($data, 0, $dataLength);
				break;
			}
			// adjust receive window
			$bytesToRead = min($bytesToRead, $packetLength - strlen($data) + 1);
		}

		return $data;
	}

}
 