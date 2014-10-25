<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

/**
 * Expression value that contains properties (i.e. a container).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
abstract class ContainerExpressionValue extends ExpressionValue {

	/**
	 * The object properties, indexed by name.
	 *
	 * @var ExpressionValue[]
	 */
	protected $properties;


	/**
	 * @param ExpressionValue[] $properties
	 */
	function __construct($properties) {
		$this->properties = $properties;
	}

	/**
	 * @return ExpressionValue[]
	 */
	public function getRawValue() {
		return $this->properties;
	}

	/**
	 * @return ExpressionValue[]
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasProperty($name) {
		return array_key_exists($name, $this->properties);
	}

	/**
	 * @param string $name
	 * @return ExpressionValue
	 */
	public function getProperty($name) {
		return $this->properties[$name];
	}

}
 