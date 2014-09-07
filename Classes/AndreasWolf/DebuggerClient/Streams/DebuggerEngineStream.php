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
		$dataLength = 0;
		while (!feof($this->stream) || ($dataLength > 0 && strlen($data) < $dataLength)) {
			$data .= fread($this->stream, $bytesToRead);

			// the length of this data packet is written directly at the beginning of the data, separated by \0
			if ($dataLength === 0) {
				$dataLength = (int)substr($data, 0, strpos($data, "\0"));
				$dataLength += 1 + strlen((string)$dataLength);
			}

			if (strlen($data) >= $dataLength) {
				break;
			}
			// adjust receive window
			$bytesToRead = min($bytesToRead, $dataLength - strlen($data) + 1);
		}

		return $data;
	}

}
 