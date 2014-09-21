<?php
namespace AndreasWolf\DebuggerClient\Protocol\Response;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommandResult;


/**
 * Response to a command sent to continue program execution ("run", "step into", "step over", …) or explicitly ask for
 * the status ("status")
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class EngineStatusResponse implements DebuggerCommandResult {

	const STATUS_STARTING = 1;
	const STATUS_BREAK = 2;
	const STATUS_RUNNING = 3;
	const STATUS_STOPPING = 4;

	/**
	 * @var int
	 */
	protected $status;

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 * @var string
	 */
	protected $filename;

	/**
	 * @var int
	 */
	protected $lineNumber;


	public function __construct(\SimpleXMLElement $responseXml) {
		$attributes = $responseXml->attributes();
		$status = $attributes['status'];
		$this->setStatus($status);

		$this->reason = $attributes['reason'];

		$this->parseFileAndLineNumber($responseXml);
	}

	/**
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return bool
	 */
	public function hasFilename() {
		return $this->filename !== NULL;
	}

	/**
	 * @return mixed
	 */
	public function getFilename() {
		return $this->filename;
	}

	/**
	 * @return mixed
	 */
	public function getLineNumber() {
		return $this->lineNumber;
	}

	protected function setStatus($status) {
		switch ($status) {
			case 'starting':
				$this->status = self::STATUS_STARTING;
				break;

			case 'break':
				$this->status = self::STATUS_BREAK;
				break;

			case 'running':
				$this->status = self::STATUS_RUNNING;
				break;

			case 'stopping':
				$this->status = self::STATUS_STOPPING;
				break;

			default:
				throw new \InvalidArgumentException('Unknown response status ' . $status, 1410712854);
		}
	}

	/**
	 * @param \SimpleXMLElement $responseXml
	 */
	protected function parseFileAndLineNumber(\SimpleXMLElement $responseXml) {
		/** @var \SimpleXMLElement[] $children */
		$children = $responseXml->children('http://xdebug.org/dbgp/xdebug');
		if (count($children) > 0 && $children[0]->getName() == 'message') {
			$childAttributes = $children[0]->attributes();
			// casting is necessary because the values of attributes() return array are SimpleXML elements
			$this->filename = (string)$childAttributes['filename'];
			$this->lineNumber = (int)$childAttributes['lineno'];
		}
	}

}
