<?php
namespace AndreasWolf\DebuggerClient\Streams;


/**
 * A component that can received and process data coming from a stream.
 *
 * This is used to decouple a session from its stream; by using the data sink in between, the session does not have
 * to know anything about where the data comes from.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface StreamDataSink {

	/**
	 * Returns a unique identifier for this sink, to make it possible to address data packets from this sink's stream.
	 *
	 * @return string
	 */
	public function getIdentifier();

	/**
	 * Processes the given incoming message.
	 *
	 * @param string $data
	 * @return void
	 */
	public function processMessage($data);

}
