<?php
namespace AndreasWolf\DebuggerClient\Tests\Unit\Session;

use AndreasWolf\DebuggerClient\Protocol\DebuggerCommand;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Unit\UnitTestCase;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function newSessionIsNotInitialized() {
		$session = new DebugSession();
		$this->assertFalse($session->isInitialized());
	}

	/**
	 * @test
	 */
	public function transactionsAreCorrectlyCreated() {
		$session = new DebugSession();
		/** @var DebuggerCommand $command */
		$command = $this->getMock('AndreasWolf\DebuggerClient\Protocol\DebuggerCommand');

		$transaction = $session->startTransaction($command);

		$this->assertSame(0, $transaction->getId());
	}

	/**
	 * @test
	 */
	public function consecutiveTransactionsHaveDifferentIds() {
		$session = new DebugSession();
		/** @var DebuggerCommand $command */
		$command = $this->getMock('AndreasWolf\DebuggerClient\Protocol\DebuggerCommand');

		$transactionA = $session->startTransaction($command);
		$transactionB = $session->startTransaction($command);

		$this->assertNotEquals($transactionA->getId(), $transactionB->getId());
	}

}
