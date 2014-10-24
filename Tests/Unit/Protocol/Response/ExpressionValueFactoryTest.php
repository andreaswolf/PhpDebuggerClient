<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Response;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValueFactory;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


class ExpressionValueFactoryTest extends UnitTestCase {

	public function propertyResponseProvider() {
		return array(
			'boolean TRUE' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="9"><property name="$booleanTrue" fullname="$booleanTrue" address="140421219436384" type="bool"><![CDATA[1]]></property></response>',
				ExpressionValue::TYPE_BOOLEAN,
				TRUE
			),
			'boolean FALSE' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="10"><property name="$booleanFalse" fullname="$booleanFalse" address="140421219436432" type="bool"><![CDATA[0]]></property></response>',
				ExpressionValue::TYPE_BOOLEAN,
				FALSE
			),
			'string' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="11"><property name="$string" fullname="$string" address="140421219436336" type="string" size="11" encoding="base64"><![CDATA[bG9yZW0gaXBzdW0=]]></property></response>',
				ExpressionValue::TYPE_STRING,
				'lorem ipsum'
			),
			'empty string' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="12"><property name="$emptyString" fullname="$emptyString" address="140421219442296" type="string" size="0" encoding="base64"><![CDATA[]]></property></response>',
				ExpressionValue::TYPE_STRING,
				''
			),
			'positive integer value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="13"><property name="$integer" fullname="$integer" address="140421219446992" type="int"><![CDATA[42]]></property></response>',
				ExpressionValue::TYPE_INTEGER,
				42
			),
			'positive float value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="14"><property name="$float" fullname="$float" address="140421219447128" type="float"><![CDATA[3.1415]]></property></response>',
				ExpressionValue::TYPE_FLOAT,
				3.1415
			),
			'NULL value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="15"><property name="$null" fullname="$null" address="140421219447264" type="null"></property></response>',
				ExpressionValue::TYPE_NULL,
				NULL
			),
		);
	}

	/**
	 * @test
	 * @dataProvider propertyResponseProvider
	 *
	 * @param string $responseXml
	 * @param int $dataType
	 * @param mixed $value
	 */
	public function propertyValueIsCorrectlyExtracted($responseXml, $dataType, $value) {
		$subject = new ExpressionValueFactory();
		$expressionValue = $subject->createValueObject(simplexml_load_string($responseXml));

		$this->assertSame($value, $expressionValue->getRawValue());
	}

	/**
	 * @test
	 * @dataProvider propertyResponseProvider
	 *
	 * @param string $responseXml
	 * @param int $dataType
	 * @param mixed $value
	 */
	public function propertyTypeIsCorrectlyExtracted($responseXml, $dataType, $value) {
		$subject = new ExpressionValueFactory();
		$expressionValue = $subject->createValueObject(simplexml_load_string($responseXml));

		$this->assertEquals($dataType, $expressionValue->getDataType());
	}

}
