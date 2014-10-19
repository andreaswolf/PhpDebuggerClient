<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\BreakpointEvent;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\Breakpoint;
use AndreasWolf\DebuggerClient\Protocol\Breakpoint\LineBreakpoint;
use AndreasWolf\DebuggerClient\Protocol\DebuggerBaseCommand;
use AndreasWolf\DebuggerClient\Session\DebugSession;


/**
 * Sets a breakpoint in the debugger engine.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class BreakpointSet extends Deferrable {

	/**
	 * @var Breakpoint
	 */
	protected $breakpoint;

	public function __construct(DebugSession $session, Breakpoint $breakpoint) {
		parent::__construct($session);

		$this->breakpoint = $breakpoint;
	}

	/**
	 * Returns the name as used in the protocol, i.e. lowercased and word parts separated with underscores.
	 *
	 * @return string
	 */
	public function getNameForProtocol() {
		return 'breakpoint_set';
	}

	/**
	 * Returns this command’s arguments, without the transaction id.
	 *
	 * @return string
	 */
	public function getArgumentsAsString() {
		switch (TRUE) {
			case $this->breakpoint instanceof LineBreakpoint:
				return '-t line -f ' . $this->breakpoint->getFile() . ' -n ' . $this->breakpoint->getLine();
				break;

			default:
				throw new \RuntimeException('Unsupported breakpoint implementation: ' . get_class($this->breakpoint));
		}
	}

	/**
	 * Processed the XML sent by the debugger engine, possibly using a callback set via `onProcessedResponse()`.
	 *
	 * @param \SimpleXMLElement $responseXmlNode
	 * @return void
	 */
	public function processResponse(\SimpleXMLElement $responseXmlNode) {
		if ($responseXmlNode->error->count() > 0) {
			$this->reject();
		} else {
			Bootstrap::getInstance()->getEventDispatcher()->dispatch(
				'session.breakpoint.set', new BreakpointEvent($this->breakpoint, $this->session)
			);

			$this->resolve(1);
		}
	}

}
