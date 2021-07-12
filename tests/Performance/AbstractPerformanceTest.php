<?php

namespace Tests\Smalot\PdfParser\Performance;

abstract class AbstractPerformanceTest {

    /**
     * Initializes the test (eg, fetches the files etc).
     *
     * @return void
     */
    abstract public function init();

    /**
     * Executes the test.
     *
     * @return void
     */
    abstract public function run();

    /**
     * Returns the time over which the test is considered a fail.
     *
     * @return int
     */
    abstract public function getMaxEstimatedTime();

}

?>