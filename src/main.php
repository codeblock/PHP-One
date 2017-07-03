<?php
require __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'require.php';

// $elapsed = microtime(true);

//Test\CTest::instance()->test();
Module\Protocol\CProtocol::instance()->run();

// $elapsed = number_format(microtime(true) - $elapsed, 5);
// echo PHP_EOL . $elapsed . PHP_EOL;