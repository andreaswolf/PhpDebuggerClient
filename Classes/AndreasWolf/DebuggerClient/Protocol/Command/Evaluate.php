<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\Response\EvalResponse;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValueFactory;
use AndreasWolf\DebuggerClient\Session\DebugSession;


/**
 * Command to evaluate an expression and return the result.
 */
class Evaluate extends Deferrable {

	/**
	 * @var string
	 */
	protected $expression;


	/**
	 * @param string $expression The expression to evaluate, as a directly evaluatable string
	 * @param DebugSession $session
	 */
	public function __construct($expression, DebugSession $session) {
		$this->expression = $expression;
		parent::__construct($session);
	}

	/**
	 * Returns the name as used in the protocol, i.e. lowercased and word parts separated with underscores.
	 *
	 * @return string
	 */
	public function getNameForProtocol() {
		return 'eval';
	}

	/**
	 * Returns this command’s arguments, without the transaction id.
	 *
	 * @return string
	 */
	public function getArgumentsAsString() {
		return '-- ' . base64_encode($this->expression);
	}

	/**
	 * Processed the XML sent by the debugger engine, possibly using a callback set via `onProcessedResponse()`.
	 *
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return void
	 */
	public function processResponse(\SimpleXMLElement $responseXmlNode) {
		if ($responseXmlNode->error->count() > 0) {
			$this->reject();
		} else {
			$value = $this->readValueFromResponseXml($responseXmlNode);
			$this->response = new EvalResponse($value, TRUE);

			$this->resolve($value);
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
