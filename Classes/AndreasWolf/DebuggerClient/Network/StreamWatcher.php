<?php
namespace AndreasWolf\DebuggerClient\Network;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;


/**
 * Watches a couple of streams and triggers events on incoming data.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StreamWatcher {

	/**
	 * Watches the given streams for incoming data and triggers the corresponding handler (defined
	 * in the stream wrapper) when data arrives
	 *
	 * @param StreamWrapper[] $streamWrappers
	 */
	public function watchAndNotify(array $streamWrappers) {
		$emptyArray = array();
		$streamsToWatch = $this->getStreamsFromWrappers($streamWrappers);

		if (stream_select($streamsToWatch, $emptyArray, $emptyArray, NULL)) {
			// $streamsToWatch is now a selection of the streams we should read data from; streams without data have
			// been removed
			foreach ($streamsToWatch as $stream) {
				$wrapper = $this->getWrapperForStream($streamWrappers, $stream);

				$wrapper->notify();
			}
		}
	}

	/**
	 * Extracts the streams from a given array of stream wrapper objects
	 *
	 * @param StreamWrapper[] $streamWrappers
	 * @return array
	 */
	protected function getStreamsFromWrappers(array $streamWrappers) {
		$streams = array();
		/** @var StreamWrapper $wrapper */
		foreach ($streamWrappers as $wrapper) {
			$streams[] = $wrapper->getStream();
		}
		return $streams;
	}

	/**
	 * Returns the wrapper for the given stream from a list of wrappers.
	 *
	 * @param StreamWrapper[] $streamWrappers
	 * @param resource $stream
	 * @return StreamWrapper
	 */
	protected function getWrapperForStream($streamWrappers, $stream) {
		/** @var StreamWrapper $wrapper */
		foreach ($streamWrappers as $wrapper) {
			if ($wrapper->getStream() === $stream) {
				return $wrapper;
			}
		}
	}

}
