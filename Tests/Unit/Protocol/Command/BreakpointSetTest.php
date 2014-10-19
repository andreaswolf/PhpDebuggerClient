<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\Command\BreakpointSet;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class BreakpointSetTest extends UnitTestCase {

	protected function setUp() {
		$loader = new \Mockery\Loader();
		$loader->register();
	}

	/**
	 * @test
	 */
	public function promiseIsRejectedOnError() {
		$subject = new BreakpointSet(
			$this->getMock('AndreasWolf\DebuggerClient\Session\DebugSession'),
			$this->getMock('AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint')
		);

		$spy = \Mockery::mock('\StdClass');
		$spy->shouldReceive('reject')->once();
		$subject->promise()->then(array($spy, 'resolve'), array($spy, 'reject'));

		$responseXml = simplexml_load_string('<response command="mock_command" transaction_id="1234">
	<error code="206" apperr="some_error">
		<message>UI Usable Message</message>
	</error>
</response>');
		$subject->processResponse($responseXml);
	}

	public function tearDown() {
		\Mockery::close();
	}

}
