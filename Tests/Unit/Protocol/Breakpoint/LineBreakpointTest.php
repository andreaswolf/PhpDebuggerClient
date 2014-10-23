<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Breakpoint;

use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class LineBreakpointTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function filePathIsCanonicalizedWithFileProtocol() {
		$subject = new LineBreakpoint('/some/file/path.php', 1);

		$this->assertStringStartsWith('file:///', $subject->getFile());
	}

	/**
	 * @test
	 */
	public function filePathWithProtocolIsLeftUnchanged() {
		$path = 'file:///some/file/path.php';
		$subject = new LineBreakpoint($path, 1);

		$this->assertStringStartsWith($path, $subject->getFile());
	}

}
