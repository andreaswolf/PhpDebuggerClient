<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

/**
 * An object as returned by the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Object extends ContainerExpressionValue {

	/**
	 * The object class
	 *
	 * @var string
	 */
	protected $class;


	/**
	 * @param int $class
	 * @param ExpressionValue[] $properties
	 */
	function __construct($class, $properties) {
		$this->class = $class;
		parent::__construct($properties);
	}

}
