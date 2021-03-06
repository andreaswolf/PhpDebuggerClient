<?php
namespace AndreasWolf\DebuggerClient\Tests\Functional\Protocol;

use AndreasWolf\DebuggerClient\Protocol\DebuggerEngineMessageParser;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DebuggerClient\Protocol\DebugSessionCommandProcessor;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;
use AndreasWolf\DebuggerClient\Streams\StreamWrapper;
use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;


/**
 * Test case for the initialization part of a debugging session.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionInitializationTest extends FunctionalTestCase {

	/**
	 * @param DebugSession $session
	 * @return DebugSessionCommandProcessor
	 */
	protected function getMockedCommandProcessor(DebugSession $session) {
		$outStream = fopen('php://memory', 'w');
		return new DebugSessionCommandProcessor($session, new DebuggerEngineStream($outStream));
	}

	/**
	 * @test
	 */
	public function sessionIsInitializedAfterInitialPacketWasReceived() {
		$session = new DebugSession();
		$messageParser = new DebuggerEngineMessageParser($session);
		$session->setCommandProcessor($this->getMockedCommandProcessor($session));
		$messageParser->processMessage('<init appid="myApp" idekey="myIde" fileuri="file:///some/file" />');

		$this->assertTrue($session->isInitialized());
	}

}
