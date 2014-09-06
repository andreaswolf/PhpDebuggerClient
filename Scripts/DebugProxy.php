<?php

if (PHP_SAPI !== 'cli') {
	echo(sprintf("This script was executed with a '%s' PHP binary. Make sure that you specified a CLI capable PHP binary in your \$PATH.", PHP_SAPI) . PHP_EOL);
	exit(1);
}

require(__DIR__ . "/../Classes/AndreasWolf/DebuggerClient/Core/Bootstrap.php");
\AndreasWolf\DebuggerClient\Core\Bootstrap::getInstance()->run();

$application = new \AndreasWolf\DebuggerClient\Proxy\DebugProxy();
$application->attachListener(new \AndreasWolf\DebuggerClient\Proxy\PrettyPrintingListener());
$application->run();
