<?php
namespace AndreasWolf\DebuggerClient\Tests\Functional\Session;

use AndreasWolf\DebuggerClient\Core\Bootstrap;
use AndreasWolf\DebuggerClient\Event\SessionEvent;
use AndreasWolf\DebuggerClient\Protocol\Response\EngineStatusResponse;
use AndreasWolf\DebuggerClient\Session\DebugSession;
use AndreasWolf\DebuggerClient\Tests\Functional\FunctionalTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * @author Andreas Wolf <aw@foundata.net>
 */
class DebugSessionStatusTest extends FunctionalTestCase {

	/**
	 * @var DebugSession
	 */
	protected $subject;

	public function setUp() {
		$this->subject = new DebugSession();
		$this->subject->setCommandProcessor(
			$this->getMockBuilder('AndreasWolf\DebuggerClient\Protocol\DebugSessionCommandProcessor')
				->disableOriginalConstructor()->getMock()
		);
	}

	protected function initializeSession() {
		$this->subject->sessionInitializedHandler(new SessionEvent($this->subject));
	}

	/**
	 * @return EventDispatcher
	 */
	protected function getEventDispatcher() {
		return Bootstrap::getInstance()->getEventDispatcher();
	}

	/**
	 * @test
	 */
	public function stoppedSessionResponseTriggersSessionClosing() {
		$this->initializeSession();
		$responseXml = simplexml_load_string('<?xml version="1.0" encoding="iso-8859-1"?>
		<response xmlns="urn:debugger_protocol_v1" xmlns:xdebug="http://xdebug.org/dbgp/xdebug" command="run" transaction_id="14" status="stopping" reason="ok"/>');
		$fakeEngineResponse = new EngineStatusResponse($responseXml);

		$sessionStopped = $sessionClosed = FALSE;

		$this->getEventDispatcher()->addListener('session.status.changed', function(SessionEvent $event) use (&$sessionStopped, &$sessionClosed) {
			switch ($event->getSession()->getStatus()) {
				case DebugSession::STATUS_STOPPED:
					$sessionStopped = TRUE;
					break;
				case DebugSession::STATUS_CLOSED:
					$sessionClosed = TRUE;
					break;
			}
		});

		$this->subject->setStatusFromDebuggerEngine($fakeEngineResponse);

		$this->assertTrue($sessionStopped, 'Session was not stopped');
		$this->assertTrue($sessionClosed, 'Session was not closed');
	}

}
