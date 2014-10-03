<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Command;

use AndreasWolf\DebuggerClient\Protocol\Command\PropertyGet;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;
use Mockery\MockInterface;


/**
 * Test case for the PropertyGet command
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PropertyGetTest extends UnitTestCase {

	protected $successResponseXml;

	protected $errorResponseXml = <<<XML
<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="2" status="break" reason="ok">
  <error code="300">
    <message><![CDATA[can not get property]]></message>
  </error>
</response>
XML;

	protected function setUp() {
		parent::setUp();

		$loader = new \Mockery\Loader();
		$loader->register();

		$propertyValue = 'someVariableValue';
		$length = strlen($propertyValue);
		$encodedPropertyValue = base64_encode('' . $propertyValue . '');
		$this->successResponseXml = <<<XML
<?xml version="1.0" encoding="iso-8859-1"?>
 <response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="2">
   <property name="\$foo" fullname="\$foo" address="139715278711880" type="string" size="$length" encoding="base64"><![CDATA[$encodedPropertyValue]]></property>
 </response>
XML;
	}

	/**
	 * @param string $variableName
	 * @return PropertyGet
	 */
	protected function getCommandObject($variableName) {
		return new PropertyGet(new DebugSession(), $variableName);
	}

	/**
	 * @param bool $expectResolve
	 * @param mixed $expectedResolutionValue
	 * @param bool $expectReject
	 * @return MockInterface
	 */
	protected function getPromiseSpy($expectResolve = FALSE, $expectedResolutionValue = NULL,
	                                 $expectReject = FALSE) {
		$mock = \Mockery::mock('\StdClass');
		if ($expectResolve === TRUE) {
			$expectation = $mock->shouldReceive('resolve')->once();
			if ($expectedResolutionValue !== NULL) {
				$expectation->withArgs(array($expectedResolutionValue));
			}
		}
		if ($expectReject) {
			$mock->shouldReceive('reject')->once();
		}

		return $mock;
	}

	/**
	 * @test
	 */
	public function promiseGetsResolvedForSuccessfulResponse() {
		$command = $this->getCommandObject('$foo');
		$responseXml = simplexml_load_string($this->successResponseXml);
		$promiseSpy = $this->getPromiseSpy(TRUE);
		$command->promise()->then(array($promiseSpy, 'resolve'), array($promiseSpy, 'reject'));

		$command->processResponse($responseXml);

		// this assertion is just to let PHPUnit not mark this test as "risky" because it does not detect
		// Mockery’s assertions.
		$this->assertTrue($command->getResponse()->isSuccessful());
	}

	/**
	 * @test
	 */
	public function promiseResolutionGetPassedCorrectValue() {
		$command = $this->getCommandObject('$foo');
		$responseXml = simplexml_load_string($this->successResponseXml);
		$promiseSpy = $this->getPromiseSpy(TRUE, 'someVariableValue');
		$command->promise()->then(array($promiseSpy, 'resolve'), array($promiseSpy, 'reject'));

		$command->processResponse($responseXml);

		// this assertion is just to let PHPUnit not mark this test as "risky" because it does not detect
		// Mockery’s assertions.
		$this->assertTrue($command->getResponse()->isSuccessful());
	}

	/**
	 * @test
	 */
	public function base64EncodedPropertyValueIsCorrectlyExtractedFromResponseXml() {
		$command = $this->getCommandObject('$foo');
		$responseXml = simplexml_load_string($this->successResponseXml);

		$command->processResponse($responseXml);

		$this->assertEquals('someVariableValue', $command->getResponse()->getValue());
		$this->assertTrue($command->getResponse()->isSuccessful());
	}

	/**
	 * @test
	 */
	public function promiseGetsRejectedForErrorResponse() {
		$command = $this->getCommandObject('$foo');
		$responseXml = simplexml_load_string($this->errorResponseXml);
		$promiseSpy = $this->getPromiseSpy(FALSE, NULL, TRUE);
		$command->promise()->then(array($promiseSpy, 'resolve'), array($promiseSpy, 'reject'));

		$command->processResponse($responseXml);

		// this assertion is just to let PHPUnit not mark this test as "risky" because it does not detect
		// Mockery’s assertions.
		$this->assertFalse($command->getResponse()->isSuccessful());
	}

	/**
	 * @test
	 */
	public function errorInServerResponseIsCorrectlyHandled() {
		$command = $this->getCommandObject('$foo');
		$responseXml = simplexml_load_string($this->errorResponseXml);

		$command->processResponse($responseXml);

		$this->assertFalse($command->getResponse()->isSuccessful());
	}

	public function tearDown() {
		\Mockery::close();
	}

}
