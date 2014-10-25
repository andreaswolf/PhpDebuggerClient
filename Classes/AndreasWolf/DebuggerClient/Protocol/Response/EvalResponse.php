<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

use AndreasWolf\DebuggerClient\Protocol\DebuggerCommandResult;


/**
 * Response to an "eval" command.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class EvalResponse implements DebuggerCommandResult {

	/**
	 * @var ExpressionValue
	 */
	protected $value;

	public function __construct(ExpressionValue $value) {
		$this->value = $value;
	}

	/**
	 * @return ExpressionValue
	 */
	public function getValue() {
		return $this->value;
	}

}
