<?php
namespace AndreasWolf\DebuggerClient\Proxy;

/**
 * Interface for listeners attached to the debugging proxy.
 */
interface ProxyListener {

	/**
	 * @param string $data
	 * @return void
	 */
	public function receivedDebuggerData($data);

	/**
	 * @param string $data
	 * @return void
	 */
	public function receivedClientData($data);

}
