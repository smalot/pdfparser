<?php

require __DIR__.'/../../vendor/autoload.php';

use PerformanceTests\Exception\PerformanceFailException;
use PerformanceTests\Test\DocumentDictionaryCacheTest;

$tests = [
    new DocumentDictionaryCacheTest(),
];

foreach ($tests as $test) {
    $test->init();

    $startTime = microtime(true);
    $test->run();
    $endTime = microtime(true);

    $time = $endTime - $startTime;

    if ($test->getMaxEstimatedTime() <= $time) {
        throw new PerformanceFailException(sprintf('Performance failed on test "%s". Time taken was %.2f seconds, expected less than %d seconds.', get_class($test), $time, $test->getMaxEstimatedTime()));
    }
}
