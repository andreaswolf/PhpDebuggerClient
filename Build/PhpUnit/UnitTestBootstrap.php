<?php
namespace AndreasWolf\DebuggerClient\Build;

use AndreasWolf\DebuggerClient\Core\Bootstrap;


$composerAutoloader = __DIR__ . '/../../vendor/autoload.php';
if(!file_exists($composerAutoloader)) {
	exit(PHP_EOL . 'Bootstrap Error: The unit test bootstrap requires the autoloader file created at install time by Composer. Looked for "' . $composerAutoloader . '" without success.');
}
require_once($composerAutoloader);

// TODO introduce a special unit test bootstrap e.g. with a mocked event dispatcher
Bootstrap::getInstance()->run();
