<?php
namespace AndreasWolf\DebuggerClient\Protocol;


interface DebuggerCommand {

	public function getNameForProtocol();

	public function getArgumentsAsString();

}
