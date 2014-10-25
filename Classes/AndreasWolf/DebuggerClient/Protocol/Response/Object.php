<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

/**
 * An object as returned by the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Object extends ExpressionValue {

	/**
	 * The object class
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * The object properties, indexed by name.
	 *
	 * @var ExpressionValue[]
	 */
	protected $properties;


	function __construct($properties) {
		$this->properties = $properties;
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
