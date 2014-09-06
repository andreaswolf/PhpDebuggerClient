<?php
namespace AndreasWolf\DebuggerClient\Core;

/**
 * Bootstrap for the Debugger library.
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class Bootstrap {

	protected static $instance;

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected function __construct() {
	}

	public function run() {
		$this->setupAutoloader();
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

}
