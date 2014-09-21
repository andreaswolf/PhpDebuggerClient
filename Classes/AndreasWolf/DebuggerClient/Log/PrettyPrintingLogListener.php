<?php
namespace AndreasWolf\DebuggerClient\Log;
use AndreasWolf\DebuggerClient\Event\CommandEvent;
use AndreasWolf\DebuggerClient\Event\StreamDataEvent;
use AndreasWolf\DebuggerClient\Streams\DebuggerEngineStream;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


/**
 * Listener that pretty-prints the dumped data to the console
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class PrettyPrintingLogListener implements EventSubscriberInterface {

	/**
	 * @param string $data
	 * @return void
	 */
	public function receivedDebuggerData($data) {
		$xmlNode = simplexml_load_string($data);
		$this->formatAndOutputXml($xmlNode);
	}

	/**
	 * @param \SimpleXMLElement $xmlNode
	 * @param string $additionalPrefix An additional prefix for the output lines
	 */
	protected function formatAndOutputXml($xmlNode, $additionalPrefix = '') {
		$doc = new \DOMDocument();
		$doc->formatOutput = TRUE;
		$doc->loadXML($xmlNode->asXML());
		$xmlData = $doc->saveXML();
		$xmlData = str_replace("\0", "", $xmlData);

		$prefix = '[DBG]';
		if ($additionalPrefix != '') {
			$prefix .= '[' . $additionalPrefix . ']';
		}
		$this->outputData($prefix, $xmlData);
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
			if (trim($line) == '') {
				continue;
			}
			echo $prefix, " ", $line, "\n";
		}
	}

	/**
	 * Handler for a "command.sent" event.
	 *
	 * @param CommandEvent $event
	 */
	public function commandEventHandler(CommandEvent $event) {
		$command = $event->getCommand();
		$commandString = $command->getNameForProtocol();
		if ($command->getArgumentsAsString() != '') {
			$commandString .= ' ' . $command->getArgumentsAsString();
		}
		$this->outputData('[IDE]', $commandString);
	}

	/**
	 * Handler for a "command.response.processed" event.
	 *
	 * @param StreamDataEvent $event
	 */
	public function streamDataEventHandler(StreamDataEvent $event) {
		if (!($event->getStreamWrapper() instanceof DebuggerEngineStream)) {
			// we’re only interested in data coming from the debugger engine
			return;
		}
		$additionalPrefix = '';

		$data = $event->getData();
		if (strlen(trim($data)) == 0) {
			return;
		}
		$xmlNode = simplexml_load_string($data);
		if ($xmlNode->getName() == 'response') {
			$attributes = $xmlNode->attributes();
			$transactionId = (int)$attributes['transaction_id'];
			$additionalPrefix = (string)$transactionId;
		}

		$this->formatAndOutputXml($xmlNode, $additionalPrefix);
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents() {
		return array(
			'stream.data.received' => 'streamDataEventHandler',
			'command.sent' => 'commandEventHandler',
		);
	}

}
