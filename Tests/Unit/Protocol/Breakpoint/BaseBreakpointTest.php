<?php
namespace AndreasWolf\DebuggerClient\Unit\Protocol\Breakpoint;

use AndreasWolf\DebuggerClient\Protocol\Breakpoint\BaseBreakpoint;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class BaseBreakpointTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function hitCallbackIsCalledOnHit() {
		$hitCallbackSpy = \Mockery::mock();
		$hitCallbackSpy->shouldReceive('onHit')->once();
		/** @var BaseBreakpoint $subject */
		$subject = $this->getMockForAbstractClass('AndreasWolf\DebuggerClient\Protocol\Breakpoint\BaseBreakpoint');

		$subject->onHit(array($hitCallbackSpy, 'onHit'));
		$subject->hit();

		// this assertion is just to let PHPUnit not mark this test as "risky" because it does not detect
		// Mockery’s assertions.
		$this->assertTrue(TRUE);
	}

}
