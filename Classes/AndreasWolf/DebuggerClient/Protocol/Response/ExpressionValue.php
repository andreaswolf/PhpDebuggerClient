<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

/**
 * An expression value reported by the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionValue {

	const TYPE_UNKNOWN = 0;
	const TYPE_OBJECT  = 1;
	const TYPE_BOOLEAN = 2;
	const TYPE_STRING  = 3;
	const TYPE_INTEGER = 4;
	const TYPE_FLOAT   = 5;
	const TYPE_NULL    = 6;
	const TYPE_ARRAY   = 7;

	/**
	 * The raw data value.
	 *
	 * @var mixed
	 */
	protected $rawValue;

	/**
	 * The data type as reported by the debugger engine.
	 *
	 * @var string
	 */
	protected $dataType = ExpressionValue::TYPE_UNKNOWN;


	/**
	 * @param int $dataType
	 * @param mixed $value
	 */
	public function __construct($dataType, $value) {
		$this->dataType = $dataType;
		$this->rawValue = $value;
	}

	/**
	 * @return string
	 */
	public function getDataType() {
		return $this->dataType;
	}

	/**
	 * @return mixed
	 */
	public function getRawValue() {
		return $this->rawValue;
	}

	/**
	 * @param mixed $comparable
	 * @return bool
	 */
	public function equalTo($comparable) {
		// TODO check if we need an additional data type comparison
		return $comparable === $this->rawValue;
	}

}
