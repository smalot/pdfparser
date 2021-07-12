<?php

require_once __DIR__.'/../../alt_autoload.php-dist';

/*
 * Load classes from Exeception and Test folders.
 */
requireFilesOfFolder(__DIR__.'/Exception');
requireFilesOfFolder(__DIR__.'/Test');


$tests = [
    new \Tests\Smalot\PdfParser\Performance\Test\DocumentDictionaryCacheTest(),
];

foreach ($tests as $test) { /* @var $test \Tests\Smalot\PdfParser\Performance\Test\AbstractPerformanceTest */
    $test->init();

    $startTime = microtime(true);
    $test->run();
    $endTime = microtime(true);

    $time = $endTime - $startTime;

    if ($test->getMaxEstimatedTime() < $time) {
        throw new \Tests\Smalot\PdfParser\Performance\Exception\PerformanceFailException(sprintf('Performance failed on test "%s". Time taken was %.2f seconds, expected less than %d seconds.', get_class($test), $time, $test->getMaxEstimatedTime()));
    }
}
