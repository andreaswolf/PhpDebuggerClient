<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Response;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class ExpressionValueTest extends UnitTestCase {

	public function equalValueDataProvider() {
		return array(
			'boolean' => array(
				ExpressionValue::TYPE_BOOLEAN, TRUE, TRUE
			),
			// TODO implement the other types
		);
	}

	/**
	 * @test
	 * @dataProvider equalValueDataProvider
	 */
	public function equalToReturnsTrueForSameValue($dataType, $rawValue, $comparisonValue) {
		$subject = new ExpressionValue($dataType, $rawValue);

		$this->assertTrue($subject->equalTo($comparisonValue));
	}

	public function notEqualValueDataProvider() {
		return array(
			'boolean TRUE expression value, integer 0' => array(
				ExpressionValue::TYPE_BOOLEAN, TRUE, 0
			),
			'boolean TRUE expression value, integer 1' => array(
				ExpressionValue::TYPE_BOOLEAN, TRUE, 1
			),
			'boolean FALSE expression value, integer 1' => array(
				ExpressionValue::TYPE_BOOLEAN, FALSE, 1
			),
			// TODO implement the other types
		);
	}

	/**
	 * @test
	 * @dataProvider notEqualValueDataProvider
	 */
	public function equalToReturnsFalseForIntegerOnBooleanValue($dataType, $rawValue, $comparisonValue) {
		$subject = new ExpressionValue($dataType, $rawValue);

		$this->assertFalse($subject->equalTo(0));
	}

}
 