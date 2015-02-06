<?php
namespace AndreasWolf\DebuggerClient\Core;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Bootstrap for the Debugger library.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Bootstrap {

	/**
	 * @var self
	 */
	protected static $instance;

	/**
	 * The event dispatcher instance
	 *
	 * @var EventDispatcherInterface
	 */
	protected $eventDispatcher;

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
	}

	/**
	 * @return EventDispatcherInterface
	 */
	public function getEventDispatcher() {
		return $this->eventDispatcher;
	}

	/**
	 * @param EventDispatcherInterface $dispatcher
	 */
	public function injectEventDispatcher(EventDispatcherInterface $dispatcher) {
		$this->eventDispatcher = $dispatcher;
	}

	public function run() {
		$this->setupAutoloader();
		$this->setupEventDispatcher();
	}

	public function setupAutoloader() {
		if (!defined('COMPOSER_AUTOLOADER_FILE')) {
			foreach (array(__DIR__ . '/../../../../../../autoload.php', __DIR__ . '/../../../../vendor/autoload.php') as $file) {
				if (file_exists($file)) {
					define('COMPOSER_AUTOLOADER_FILE', $file);
					break;
				}
			}
		}

		require COMPOSER_AUTOLOADER_FILE;
	}

	protected function setupEventDispatcher() {
		$this->eventDispatcher = new EventDispatcher();
	}

}
