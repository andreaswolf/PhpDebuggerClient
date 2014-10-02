<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommandResult;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PropertyGetResponse implements DebuggerCommandResult {

	/**
	 * @var bool
	 */
	protected $successful;

	/**
	 * @var mixed
	 */
	protected $value;

	public function __construct($value, $success = TRUE) {
		$this->value = $value;
		$this->successful = $success;
	}

	/**
	 * @return boolean
	 */
	public function isSuccessful() {
		return $this->successful;
	}

	/**
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

}
