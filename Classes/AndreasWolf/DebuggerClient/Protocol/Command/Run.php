<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;
use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use AndreasWolf\DebuggerClient\Protocol\Response\EngineStatusResponse;


/**
 * Command to run the debugger session
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Run extends DebuggerBaseCommand {

	public function getNameForProtocol() {
		return 'run';
	}

	public function getArgumentsAsString() {
		return '';
	}

	public function processResponse(\SimpleXMLElement $responseXmlNode) {
		$this->response = new EngineStatusResponse($responseXmlNode);
	}

}
 