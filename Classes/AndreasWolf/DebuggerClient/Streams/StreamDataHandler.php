<?php
namespace AndreasWolf\DebuggerClient\Streams;

/**
 * A handler for data coming from a stream
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface StreamDataHandler {

	public function handleIncomingData();

}
