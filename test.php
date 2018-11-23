<?php

use ApolloRIP\Logchecker\Logchecker;

include('vendor/autoload.php');

$end = php_sapi_name() === 'cli' ? "\n" : '<br />';
$logchecker = new Logchecker();
$logchecker->newFile('tests/logs/swedish_99_1.log');
print('Logchecker Version: ' . $logchecker->getLogcheckerVersion() . $end);
print('Rip Program: ' . $logchecker->getProgram() . $end);
$logchecker->parse();
print('Score: ' . $logchecker->getScore() . $end);
print('Checksum: ' . ($logchecker->hasValidChecksum() ? 'true' : 'false') . $end);
var_dump($logchecker->getDetails());

