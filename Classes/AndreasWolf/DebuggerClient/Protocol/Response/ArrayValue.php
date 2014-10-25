<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;


class ArrayValue extends ContainerExpressionValue {

	public function getDataType() {
		return ExpressionValue::TYPE_ARRAY;
	}


}
