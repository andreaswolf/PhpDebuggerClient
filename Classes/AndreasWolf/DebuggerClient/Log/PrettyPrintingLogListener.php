<?php
namespace AndreasWolf\DebuggerClient\Log;
use AndreasWolf\DebuggerClient\Proxy\ProxyListener;


/**
 * Listener that pretty-prints the dumped data to the console
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PrettyPrintingLogListener implements ProxyListener {

	/**
	 * @param string $data
	 * @return void
	 */
	public function receivedDebuggerData($data) {
		$data = substr($data, strpos($data, "\0") + 1);

		$xmlNode = simplexml_load_string($data);
		$doc = new \DOMDocument();
		$doc->formatOutput = TRUE;
		$doc->loadXML($xmlNode->asXML());
		$data = $doc->saveXML();
		$data = str_replace("\0", "", $data);

		$this->outputData('[DBG]', $data);
	}

	/**
	 * @param string $data
	 * @return void
	 */
	public function receivedClientData($data) {
		// sometimes two commands are sent within one package, separated by NULL-characters
		$data = str_replace("\0", "\n", $data);
		$expressionMarker = strpos($data, '--');
		if ($expressionMarker !== FALSE) {
			$data = substr($data, 0, $expressionMarker) . '-- ' . base64_decode(substr($data, $expressionMarker + 2));
		}

		$this->outputData('[IDE]', $data);
	}

	protected function outputData($prefix, $data) {
		$lines = explode("\n", $data);
		foreach ($lines as $line) {
			echo $prefix, " ", $line, "\n";
		}
	}

}
