<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;

/**
 * Factory for expression value objects.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ExpressionValueFactory {

	/**
	 * @param \SimpleXMLElement $xmlElement
	 * @return ExpressionValue
	 */
	public function createValueObject($xmlElement) {
		/** @var \SimpleXMLElement $node */
		$node = $xmlElement->property;

		return $this->createValueObjectForPropertyNode($node);
	}

	/**
	 * @param $node
	 * @return ExpressionValue
	 */
	protected function createValueObjectForPropertyNode($node) {
		$dataType = $this->getPropertyType($node);

		if ($dataType == ExpressionValue::TYPE_OBJECT) {
			$attributes = $node->attributes;
			$class = (string)$attributes['classname'];

			$properties = $this->readObjectProperties($node);

			return new Object($class, $properties);
		} elseif ($dataType == ExpressionValue::TYPE_ARRAY) {
			$properties = $this->readObjectProperties($node);

			return new ArrayValue($properties);
		} else {
			$rawValue = $this->readSimplePropertyValue($dataType, $node);

			return new ExpressionValue($dataType, $rawValue);
		}
	}

	/**
	 * Extracts the type of a property from the XML element.
	 *
	 * @param \SimpleXMLElement $propertyNode
	 * @return int
	 */
	protected function getPropertyType(\SimpleXMLElement $propertyNode) {
		$attributes = $propertyNode->attributes();

		switch ($attributes['type']) {
			case 'int':
				$dataType = ExpressionValue::TYPE_INTEGER;
				break;

			case 'float':
				$dataType = ExpressionValue::TYPE_FLOAT;
				break;

			case 'null':
				$dataType = ExpressionValue::TYPE_NULL;
				break;

			case 'string':
				$dataType = ExpressionValue::TYPE_STRING;
				break;

			case 'bool':
				$dataType = ExpressionValue::TYPE_BOOLEAN;
				break;

			case 'object':
				$dataType = ExpressionValue::TYPE_OBJECT;
				break;

			case 'array':
				$dataType = ExpressionValue::TYPE_ARRAY;
				break;

			default:
				$dataType = ExpressionValue::TYPE_UNKNOWN;
		}

		return $dataType;
	}

	/**
	 * @param int $type
	 * @param \SimpleXMLElement $propertyNode
	 * @return string
	 */
	protected function readSimplePropertyValue($type, \SimpleXMLElement $propertyNode) {
		$attributes = $propertyNode->attributes();

		$value = (string)$propertyNode;
		if ($attributes['encoding'] == 'base64') {
			$value = base64_decode($value);
		}
		switch ($type) {
			case ExpressionValue::TYPE_INTEGER:
				$rawValue = (int)$value;
				break;

			case ExpressionValue::TYPE_FLOAT:
				$rawValue = (float)$value;
				break;

			case ExpressionValue::TYPE_NULL:
				$rawValue = NULL;
				break;

			case ExpressionValue::TYPE_STRING:
				$rawValue = $value;
				break;

			case ExpressionValue::TYPE_BOOLEAN:
				$rawValue = (bool)$value;
				break;

			case ExpressionValue::TYPE_OBJECT:
			case ExpressionValue::TYPE_ARRAY:
				throw new \InvalidArgumentException('Objects and arrays don\'t contain simple values.');
				break;

			default:
				throw new \RuntimeException('Unknown type ' . $type);
		}

		return $rawValue;
	}

	/**
	 * @param \SimpleXMLElement $objectNode
	 * @return ExpressionValue[]
	 */
	protected function readObjectProperties(\SimpleXMLElement $objectNode) {
		$properties = array();
		/** @var \SimpleXMLElement $child */
		foreach ($objectNode->children() as $child) {
			$childAttributes = $child->attributes();
			$name = (string)$childAttributes['name'];
			$properties[$name] = $this->createValueObjectForPropertyNode($child);
		}

		return $properties;
	}

}
