<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol;

use AndreasWolf\DebuggerClient\Protocol\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function newSessionIsNotInitialized() {
		$session = new DebugSession();
		$this->assertFalse($session->isInitialized());
	}

}
