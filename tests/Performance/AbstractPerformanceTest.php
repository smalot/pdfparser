<?php

namespace PerformanceTests;

abstract class AbstractPerformanceTest
{
    /**
     * Initializes the test (eg, fetches the files etc).
     */
    abstract public function init(): void;

    /**
     * Executes the test.
     */
    abstract public function run(): void;

    /**
     * Returns the time over which the test is considered a fail.
     */
    abstract public function getMaxEstimatedTime(): int;
}
