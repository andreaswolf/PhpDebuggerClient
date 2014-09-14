<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Response;
use AndreasWolf\DebuggerClient\Protocol\Response\EngineStatusResponse;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class EngineStatusResponseTest extends UnitTestCase {

	public function statusDataProvider() {
		return array(
			'status starting' => array('starting', EngineStatusResponse::STATUS_STARTING),
			'status break' => array('break', EngineStatusResponse::STATUS_BREAK),
			'status stopping' => array('stopping', EngineStatusResponse::STATUS_STOPPING),
			'status running' => array('running', EngineStatusResponse::STATUS_RUNNING),
		);
	}

	/**
	 * @param string $statusString
	 * @param int $status
	 *
	 * @test
	 * @dataProvider statusDataProvider
	 */
	public function statusIsCorrectlyParsed($statusString, $status) {
		$xmlElement = simplexml_load_string(<<<DOC
<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="run" transaction_id="4" status="$statusString" reason="ok" />
DOC
		);

		$response = new EngineStatusResponse($xmlElement);

		$this->assertEquals($status, $response->getStatus());
	}

	/**
	 * @test
	 */
	public function exceptionThrownForInvalidResultString() {
		$this->setExpectedException('\\InvalidArgumentException');

		$xmlElement = simplexml_load_string(<<<DOC
<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="run" transaction_id="4" status="invalid" reason="ok" />
DOC
		);

		$response = new EngineStatusResponse($xmlElement);
	}

	/**
	 * @test
	 */
	public function filenameAndLineAreCorrectlyParsed() {
		$xmlElement = simplexml_load_string(<<<DOC
<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="step_into" transaction_id="4" status="break" reason="ok">
  <xdebug:message filename="file:///some/folder/withAFile.php" lineno="1337"/>
</response>
DOC
		);

		$response = new EngineStatusResponse($xmlElement);

		$this->assertEquals('file:///some/folder/withAFile.php', $response->getFilename());
		$this->assertEquals(1337, $response->getLineNumber());
	}

}
