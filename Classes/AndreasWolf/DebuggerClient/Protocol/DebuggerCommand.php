<?php
namespace AndreasWolf\DebuggerClient\Protocol;

/**
 * Interface for commands sent to the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
interface DebuggerCommand {

	const STATUS_NEW = 0;
	const STATUS_SENT = 1;
	/** The command received a success response */
	const STATUS_SUCCESSFUL = 2;
	/** The result of the command was an error */
	const STATUS_ERROR = 3;

	public function __construct(DebugSession $session);

	/**
	 * Returns the status of this command: if it was sent, if a response was received and if the response was
	 * successful.
	 *
	 * @return mixed
	 */
	public function getStatus();

	/**
	 * Returns the name as used in the protocol, i.e. lowercased and word parts separated with underscores.
	 * @return string
	 */
	public function getNameForProtocol();

	/**
	 * Returns this command’s arguments, without the transaction id.
	 *
	 * @return string
	 */
	public function getArgumentsAsString();

	/**
	 * Called by the command processor when the command was sent.
	 *
	 * As this is a 1:1 interaction and the processor knows the command interface, we don’t need to use an event for
	 * this.
	 *
	 * @return void
	 */
	public function onSend();

	/**
	 * Processed the XML sent by the debugger engine, possibly using a callback set via `onProcessedResponse()`.
	 *
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return void
	 */
	public function processResponse(\SimpleXMLElement $responseXmlNode);

	/**
	 * Sets a callback to call when a response is processed.
	 *
	 * This is not implemented with an event because we might have a huge number of commands with different callbacks,
	 * so using a global event might lead to a lot of unnecessary method calls.
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function onResponseProcessed($callback);

}
