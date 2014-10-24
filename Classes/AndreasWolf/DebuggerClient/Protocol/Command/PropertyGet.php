<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValueFactory;
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

			$this->reject();
		} else {
			$value = $this->readValueFromResponseXml($responseXmlNode);
			$this->response = new PropertyGetResponse($value, TRUE);

			$this->resolve($value);
		}

		// TODO remove this in favor of the resolve/reject callbacks
		if ($this->responseCallback) {
			call_user_func($this->responseCallback);
		}
	}

	/**
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return ExpressionValue
	 */
	protected function readValueFromResponseXml(\SimpleXMLElement $responseXmlNode) {
		$expressionValueFactory = new ExpressionValueFactory();

		return $expressionValueFactory->createValueObject($responseXmlNode);
	}

}
