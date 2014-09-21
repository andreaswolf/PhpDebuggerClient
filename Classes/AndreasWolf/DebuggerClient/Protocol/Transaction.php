<?php
namespace AndreasWolf\DebuggerClient\Protocol;

/**
 *
 *
 * TODO implement the notion of a "status"
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Transaction {

	/**
	 * @var DebugSession
	 */
	protected $session;

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var DebuggerCommand
	 */
	protected $command;


	public function __construct(DebugSession $session, $id, DebuggerCommand $command) {
		$this->id = $id;
		$this->command = $command;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return \AndreasWolf\DebuggerClient\Protocol\DebuggerCommand
	 */
	public function getCommand() {
		return $this->command;
	}

	/**
	 * @param \SimpleXMLElement $response
	 */
	public function finish(\SimpleXMLElement $response) {
		$this->command->processResponse($response);
	}

	/**
	 * @return DebuggerCommandResult
	 */
	public function getResult() {
		return $this->command->getResponse();
	}

}
