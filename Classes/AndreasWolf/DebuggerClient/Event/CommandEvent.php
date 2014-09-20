<?php
namespace AndreasWolf\DebuggerClient\Event;
use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;
use AndreasWolf\DebuggerClient\Protocol\DebugSession;
use Symfony\Component\EventDispatcher\Event;


/**
 * Event triggered for commands. Used to notify "outside" observers about commands, e.g. when a command was sent.
 * Most command-internal communication is handled via callbacks directly attached to a command (to reduce overhead
 * in the receivers that e.g. want to know when a breakpoint was set).
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class CommandEvent extends SessionEvent {

	/**
	 * @var DebuggerCommand
	 */
	protected $command;

	public function __construct(DebuggerCommand $command, DebugSession $session) {
		parent::__construct($session);
		$this->command = $command;
	}

}
