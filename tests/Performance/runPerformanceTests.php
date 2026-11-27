<?php

require __DIR__.'/../../vendor/autoload.php';

use PerformanceTests\Exception\PerformanceFailException;
use PerformanceTests\Test\BalancedParenthesesFormatContentTest;
use PerformanceTests\Test\DocumentDictionaryCacheTest;
use PerformanceTests\Test\InlineImageDictionaryFormatContentTest;
use PerformanceTests\Test\InlineImagesFormatContentTest;
use PerformanceTests\Test\KernedTjArrayFormatContentTest;
use PerformanceTests\Test\MarkedContentDictionariesFormatContentTest;

$tests = [
    new DocumentDictionaryCacheTest(),
    new KernedTjArrayFormatContentTest(),
    new BalancedParenthesesFormatContentTest(),
    new MarkedContentDictionariesFormatContentTest(),
    new InlineImagesFormatContentTest(),
    new InlineImageDictionaryFormatContentTest(),
];

// Run all tests, so a single run reveals every failing one
$failures = [];

foreach ($tests as $test) {
    $test->init();

    $startTime = microtime(true);
    $test->run();
    $endTime = microtime(true);

    $time = $endTime - $startTime;

    printf("%-50s %6.2f s (max. %d s)\n", (new ReflectionClass($test))->getShortName(), $time, $test->getMaxEstimatedTime());

    if ($test->getMaxEstimatedTime() <= $time) {
        $failures[] = sprintf(
            'Performance failed on test "%s". Time taken was %.2f seconds, expected less than %d seconds.',
            get_class($test),
            $time,
            $test->getMaxEstimatedTime()
        );
    }
}

if ([] !== $failures) {
    throw new PerformanceFailException(implode("\n", $failures));
}
