<?php
namespace AndreasWolf\DebuggerClient\Streams;
use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\StreamEvent;


/**
 * A stream enriched with additional metadata, like a custom data handler for incoming data.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StreamWrapper {

	/**
	 * The wrapped stream
	 *
	 * @var resource
	 */
	protected $stream;

	/**
	 * @var StreamDataHandler
	 */
	protected $dataHandler;

	const STATUS_OPEN = 1;
	const STATUS_SHUTDOWN = 2;

	protected $status = self::STATUS_OPEN;

	/**
	 * @param resource $stream The stream to wrap
	 */
	public function __construct($stream) {
		$this->stream = $stream;
	}

	/**
	 * @return resource
	 */
	public function getStream() {
		return $this->stream;
	}

	/**
	 * @param \AndreasWolf\DebuggerClient\Streams\StreamDataHandler $dataHandler
	 */
	public function setDataHandler($dataHandler) {
		$this->dataHandler = $dataHandler;
	}

	/**
	 * Notifies this stream wrapper that it stream has new data waiting to be read.
	 *
	 * @return void
	 */
	public function notify() {
		$this->dataHandler->handleIncomingData();
	}

	/**
	 * @param string $data
	 * @return void
	 * @throws \RuntimeException
	 */
	public function sendData($data) {
		if (!$this->isActive()) {
			throw new \RuntimeException('Writing data to inactive stream');
		}
		$bytesSent = $totalBytesSent = 0;
		$bytesToSend = strlen($data);
		while ($totalBytesSent < $bytesToSend) {
			$bytesSent = fwrite($this->stream, substr($data, $totalBytesSent));

			if ($bytesSent === FALSE) {
				throw new \RuntimeException('Could not write to stream');
			}

			$totalBytesSent += $bytesSent;
		}
	}

	/**
	 * Checks if this stream is active (i.e. does not have reached its end or was closed)
	 *
	 * @return bool
	 */
	public function isActive() {
		return $this->status == self::STATUS_OPEN && !feof($this->stream);
	}

	/**
	 * @return void
	 */
	public function shutdown() {
		stream_socket_shutdown($this->stream, STREAM_SHUT_RDWR);
		$this->status = self::STATUS_SHUTDOWN;

		Bootstrap::getInstance()->getEventDispatcher()->dispatch('stream.shutdown', new StreamEvent($this));
	}

}
