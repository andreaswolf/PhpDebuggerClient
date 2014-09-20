<?php
namespace Functional\Protocol;
use AndreasWolf\DebuggerClient\Protocol\Command;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;
use AndreasWolf\DebuggerClient\Protocol\DebuggerEngineMessageParser;
use AndreasWolf\DebuggerClient\Protocol\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;


/**
 * Test case for the session transaction handling.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionTransactionTest extends FunctionalTestCase {

	/**
	 * @test
	 */
	public function messageParserCorrectlyPassesTransactionResultToTransaction() {
		$session = new DebugSession();
		$messageParser = new DebuggerEngineMessageParser($session);
		$commandResult = FALSE;

		/** @var DebuggerCommand $command */
		$command = $this->getMock('AndreasWolf\DebuggerClient\Protocol\DebuggerCommand');
		$command->expects($this->once())->method('processResponse')
			->will($this->returnCallback(function(\SimpleXMLElement $response) use (&$commandResult) {
				$commandResult = TRUE;
			})
		);

		$transaction = $session->startTransaction($command);

		$messageParser->processMessage(
			'<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="mocked_command" transaction_id="0"/>'
		);

		$this->assertTrue($commandResult);
	}

}
