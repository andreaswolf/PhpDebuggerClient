<?php
namespace AndreasWolf\DebuggerClient\Protocol\Command;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;


/**
 * Command to run the debugger session
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Run implements DebuggerCommand {

	public function getNameForProtocol() {
		return 'run';
	}

	public function getArgumentsAsString() {
		return '';
	}

}
 