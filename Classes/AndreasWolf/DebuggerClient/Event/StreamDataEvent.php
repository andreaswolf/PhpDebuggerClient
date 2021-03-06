<?php
namespace AndreasWolf\DebuggerClient\Event;

use AndreasWolf\DebuggerClient\Streams\StreamWrapper;


/**
 * Event that is triggered when something data-related (read, write) has happened on a stream.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class StreamDataEvent extends StreamEvent {

	/**
	 * @var string
	 */
	protected $data;

	public function __construct(StreamWrapper $wrapper, $data) {
		parent::__construct($wrapper);
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getData() {
		return $this->data;
	}

}
