<?php
namespace AndreasWolf\DebuggerClient\Streams;

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

}
