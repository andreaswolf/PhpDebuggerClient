<?php
namespace AndreasWolf\DebuggerClient\Event;

use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use Symfony\Component\EventDispatcher\Event;


/**
 * A stream-related event.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StreamEvent extends Event {

	/**
	 * @var StreamWrapper
	 */
	protected $streamWrapper;

	/**
	 * @param StreamWrapper $wrapper
	 */
	public function __construct(StreamWrapper $wrapper) {
		$this->streamWrapper = $wrapper;
	}

	/**
	 * @return \AndreasWolf\DebuggerClient\Streams\StreamWrapper
	 */
	public function getStreamWrapper() {
		return $this->streamWrapper;
	}

}
