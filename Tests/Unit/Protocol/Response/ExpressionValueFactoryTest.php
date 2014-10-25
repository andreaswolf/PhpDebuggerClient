<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Protocol\Response;

use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue;
use AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValueFactory;
use AndreasWolf\DebuggerClient\Protocol\Response\Object;
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
			'negative integer value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="13"><property name="$integer" fullname="$integer" address="140421219446992" type="int"><![CDATA[-42]]></property></response>',
				ExpressionValue::TYPE_INTEGER,
				-42
			),
			'positive float value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="14"><property name="$float" fullname="$float" address="140421219447128" type="float"><![CDATA[3.1415]]></property></response>',
				ExpressionValue::TYPE_FLOAT,
				3.1415
			),
			'negative float value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="14"><property name="$float" fullname="$float" address="140421219447128" type="float"><![CDATA[-3.1415]]></property></response>',
				ExpressionValue::TYPE_FLOAT,
				-3.1415
			),
			'array value' => array(
				'<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="14"><property name="$array" fullname="$array" address="140421219447128" type="array"></property></response>',
				ExpressionValue::TYPE_ARRAY,
				array()
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

	/**
	 * @test
	 */
	public function objectsAreProperlyResolved() {
		$subject = new ExpressionValueFactory();
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="0" numchildren="0" page="0" pagesize="32">
				</property>
			</response>'));

		$this->assertInstanceOf('AndreasWolf\DebuggerClient\Protocol\Response\Object', $expressionValue);
	}

	/**
	 * @test
	 */
	public function objectsHaveTheirPropertiesMappedToThem() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="1" numchildren="2" page="0" pagesize="32">
					<property name="someProperty" facet="public" address="140421219447496" type="bool"><![CDATA[1]]></property>
					<property name="someOtherProperty" facet="public" address="140421219447856" type="string" size="11" encoding="base64"><![CDATA[bG9yZW0gaXBzdW0=]]></property>
				</property>
			</response>'));

		$this->assertCount(2, $expressionValue->getProperties());
		$this->assertTrue($expressionValue->hasProperty('someProperty'));
		$this->assertTrue($expressionValue->hasProperty('someOtherProperty'));
	}

	/**
	 * @test
	 */
	public function valuesOfMultipleObjectPropertiesAreResolvedCorrectly() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="1" numchildren="2" page="0" pagesize="32">
					<property name="someProperty" facet="public" address="140421219447496" type="bool"><![CDATA[1]]></property>
					<property name="someOtherProperty" facet="public" address="140421219447856" type="string" size="11" encoding="base64"><![CDATA[bG9yZW0gaXBzdW0=]]></property>
				</property>
			</response>'));

		$this->assertEquals(ExpressionValue::TYPE_BOOLEAN, $expressionValue->getProperty('someProperty')->getDataType());
		$this->assertEquals(ExpressionValue::TYPE_STRING, $expressionValue->getProperty('someOtherProperty')->getDataType());
	}

	/**
	 * @test
	 */
	public function objectPropertiesAreMappedToObjectsOfCorrectType() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="1" numchildren="1" page="0" pagesize="32">
					<property name="someProperty" facet="public" address="140421219447496" type="bool"><![CDATA[1]]></property>
				</property>
			</response>'));

		$propertyObject = $expressionValue->getProperty('someProperty');
		$this->assertInternalType('object', $propertyObject);
		$this->assertInstanceOf('AndreasWolf\DebuggerClient\Protocol\Response\ExpressionValue', $propertyObject);
	}

	public function objectPropertyResponseProvider() {
		return array(
			'boolean TRUE' => array(
				'<property name="subject" facet="public" address="140421219447496" type="bool"><![CDATA[1]]></property>',
				ExpressionValue::TYPE_BOOLEAN,
				TRUE
			),
			'boolean FALSE' => array(
				'<property name="subject" facet="public" address="140421219447720" type="bool"><![CDATA[0]]></property>',
				ExpressionValue::TYPE_BOOLEAN,
				FALSE
			),
			'string' => array(
				'<property name="subject" facet="public" address="140421219447856" type="string" size="11" encoding="base64"><![CDATA[bG9yZW0gaXBzdW0=]]></property>',
				ExpressionValue::TYPE_STRING,
				'lorem ipsum'
			),
			'empty string' => array(
				'<property name="subject" facet="public" address="140421219447992" type="string" size="0" encoding="base64"><![CDATA[]]></property>',
				ExpressionValue::TYPE_STRING,
				''
			),
			'positive integer value' => array(
				'<property name="subject" facet="public" address="140421219448128" type="int"><![CDATA[42]]></property>',
				ExpressionValue::TYPE_INTEGER,
				42
			),
			'positive float value' => array(
				'<property name="subject" facet="public" address="140421219448264" type="float"><![CDATA[3.1415]]></property>',
				ExpressionValue::TYPE_FLOAT,
				3.1415
			),
			'NULL value' => array(
				'<property name="subject" facet="public" address="140421219448400" type="null"></property>',
				ExpressionValue::TYPE_NULL,
				NULL
			),
		);
	}

	/**
	 * @param string $xmlSnippet
	 * @param int $expectedType
	 * @param mixed $expectedValue
	 *
	 * @test
	 * @dataProvider objectPropertyResponseProvider
	 */
	public function objectPropertiesAreCorrectlyDecoded($xmlSnippet, $expectedType, $expectedValue) {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="1" numchildren="1" page="0" pagesize="32">'
			. $xmlSnippet . '
				</property>
			</response>'));

		$propertyObject = $expressionValue->getProperty('subject');
		$this->assertSame($expectedValue, $propertyObject->getRawValue());
		$this->assertEquals($expectedType, $propertyObject->getDataType());
	}

	/**
	 * @test
	 */
	public function objectClassIsCorrectlyExtracted() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="eval" transaction_id="pebugger-16.property_get.">
				<property address="140735948082816" type="object" classname="stdClass" children="0" numchildren="0" page="0" pagesize="32">
				</property>
			</response>'));

		$this->assertEquals('stdClass', $expressionValue->getClass());
	}

	/**
	 * @test
	 */
	public function arrayWithNumericKeyIsCorrectlyDecoded() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="pebugger-13.property_get.">
				<property name="$array" fullname="$array" address="140330420476216" type="array" children="1" numchildren="2" page="0" pagesize="32">
					<property name="0" fullname="$array[0]" address="140330420475312" type="int"><![CDATA[1]]></property>
					<property name="1" fullname="$array[1]" address="140330420475448" type="int"><![CDATA[2]]></property>
				</property>
			</response>'));

		$firstArrayEntry = $expressionValue->getProperty(0);
		$this->assertSame(1, $firstArrayEntry->getRawValue());

		$secondArrayEntry = $expressionValue->getProperty(1);
		$this->assertSame(2, $secondArrayEntry->getRawValue());
	}

	/**
	 * @test
	 */
	public function hashmapIsCorrectlyDecoded() {
		$subject = new ExpressionValueFactory();
		/** @var Object $expressionValue */
		$expressionValue = $subject->createValueObject(simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
			<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="property_get" transaction_id="pebugger-13.property_get.">
				<property name="$array" fullname="$array" address="140330420476216" type="array" children="1" numchildren="2" page="0" pagesize="32">
					<property name="foo" fullname="$array[foo]" address="140330420475312" type="string" size="11" encoding="base64"><![CDATA[bG9yZW0gaXBzdW0=]]></property>
					<property name="bar" fullname="$array[bar]" address="140330420475448" type="string" size="0" encoding="base64"><![CDATA[]]></property>
				</property>
			</response>'));

		$firstArrayEntry = $expressionValue->getProperty('foo');
		$this->assertSame('lorem ipsum', $firstArrayEntry->getRawValue());

		$secondArrayEntry = $expressionValue->getProperty('bar');
		$this->assertSame('', $secondArrayEntry->getRawValue());
	}

}
