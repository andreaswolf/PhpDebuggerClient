<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Streams;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;
use AndreasWolf\DebuggerClient\Streams\StreamDataSink;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


/**
 *
 */
class DebuggerEngineStreamTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function shortMessageIsCorrectlyReadFromStream() {
		$this->markTestSkipped('This needs to be refactored, see <https://github.com/andreaswolf/PhpDebuggerClient/issues/10>');

		// The 500 bytes used here are about the size of e.g. a regular "init" message
		$contents = $this->getRandomString(500);
		$streamContents = "500\0$contents\0";

		$streamHandle = fopen('php://memory', 'rw');
		fputs($streamHandle, $streamContents);
		fseek($streamHandle, 0);

		/** @var StreamDataSink $mockedSink */
		$mockedSink = $this->getMock('AndreasWolf\DebuggerClient\Streams\StreamDataSink');
		$mockedSink->expects($this->once())->method('processMessage')->with($this->equalTo($contents));

		$wrapper = new DebuggerEngineStream($streamHandle);
		$wrapper->setSink($mockedSink);
		$wrapper->handleIncomingData();
	}

	/**
	 * @param int $length
	 * @return string
	 */
	protected function getRandomString($length) {
		$characterList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$repeatedList = str_repeat($characterList, ceil($length / 4));

		$randomString = substr(str_shuffle($repeatedList), 0, $length);
		return $randomString;
	}

}
