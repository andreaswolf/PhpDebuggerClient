<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use AndreasWolf\DebuggerClient\Protocol\Response\PropertyGetResponse;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use React\Promise;


/**
 * Command to fetch a property
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PropertyGet extends Deferrable {

	protected $name;

	/**
	 * @param DebugSession $session
	 * @param string $longName
	 */
	public function __construct(DebugSession $session, $longName) {
		$this->name = $longName;
		parent::__construct($session);
	}

	/**
	 * Returns the name as used in the protocol, i.e. lowercased and word parts separated with underscores.
	 *
	 * @return string
	 */
	public function getNameForProtocol() {
		return 'property_get';
	}

	/**
	 * Returns this command’s arguments, without the transaction id.
	 *
	 * @return string
	 */
	public function getArgumentsAsString() {
		return '-n ' . $this->name;
	}

	/**
	 * Processed the XML sent by the debugger engine, possibly using a callback set via `onProcessedResponse()`.
	 *
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return void
	 */
	public function processResponse(\SimpleXMLElement $responseXmlNode) {
		if ($responseXmlNode->error->count() > 0) {
			$this->response = new PropertyGetResponse(NULL, FALSE);

			if ($this->promise && is_callable($this->rejectCallback)) {
				call_user_func($this->rejectCallback);
			}
		} else {
			$value = $this->readValueFromResponseXml($responseXmlNode);
			$this->response = new PropertyGetResponse($value, TRUE);

			if ($this->promise && is_callable($this->resolveCallback)) {
				call_user_func($this->resolveCallback, $value);
			}
		}

		// TODO remove this in favor of the resolve/reject callbacks
		if ($this->responseCallback) {
			call_user_func($this->responseCallback);
		}
	}

	/**
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return string
	 */
	protected function readValueFromResponseXml(\SimpleXMLElement $responseXmlNode) {
		/** @var \SimpleXMLElement $propertyNode */
		$propertyNode = $responseXmlNode->property;
		$attributes = $propertyNode->attributes();

		$value = (string)$propertyNode;
		if ($attributes['encoding'] == 'base64') {
			$value = base64_decode($value);
		}

		return $value;
	}

}
