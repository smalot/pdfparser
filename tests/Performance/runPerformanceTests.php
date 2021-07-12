<?php

include __DIR__.'/../../vendor/autoload.php';

$tests = [
    new \Tests\Smalot\PdfParser\Performance\DocumentDictionaryCacheTest(),
];

foreach ($tests as $test) { /* @var $test \Tests\Smalot\PdfParser\Performance\AbstractPerformanceTest */
    $test->init();

    $startTime = microtime(true);
    $test->run();
    $endTime = microtime(true);

    $time = $endTime - $startTime;

    if ($test->getMaxEstimatedTime() < $time) {
        throw new \Tests\Smalot\PdfParser\Performance\PerformanceFailException(sprintf('Performance failed on test "%s". Time taken was %.2f seconds, expected less than %d seconds.', get_class($test), $time, $test->getMaxEstimatedTime()));
    }
}
