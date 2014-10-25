<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Command;
use AndreasWolf\DebuggerClient\Protocol\Command\Evaluate;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class EvaluateTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function expressionIsProperlyEncodedIntoCommandArgument() {
		$subject = new Evaluate('$foo->bar()', $this->getMockedSession());

		$this->assertEquals('-- ' . base64_encode('$foo->bar()'), $subject->getArgumentsAsString());
	}

	/**
	 * @return DebugSession
	 */
	protected function getMockedSession() {
		return $this->getMockBuilder('AndreasWolf\DebuggerClient\Session\DebugSession')->disableOriginalConstructor()->getMock();
	}

}
