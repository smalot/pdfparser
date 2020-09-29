<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-02
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
 *
 * @license LGPLv3
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Tests\Smalot\PdfParser;

use PHPUnit\Framework\TestCase as PHPTestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Parser;

abstract class TestCase extends PHPTestCase
{
    /**
     * Contains an instance of the class to test.
     */
    protected $fixture;

    protected $rootDir;

    private $errorHandlerChanged = false;

    private $catchAllErrorHandler;

    function __construct() {
        parent::__construct();

        // PHP does not implement setting a class property to an anonymous function,
        // so we have to do it in the constructor.
        $this->catchAllErrorHandler = function ($typeNumber, $message, $file, $lineNumber) {
            $this->fail(
                sprintf('%s: "%s" in %s:%d',
                    $this->getErrorType($typeNumber),
                    $message,
                    $file,
                    $lineNumber
                )
            );
        };
    }

    public function setUp()
    {
        parent::setUp();

        $this->rootDir = __DIR__.'/..';
    }

    public function tearDown() {
        // if we changed the error handler using catchAllErrors(), reset it now
        if($this->errorHandlerChanged) {
            restore_error_handler();
        }
    }

    /**
     * This temporarily changes the PHP-internal error handler
     * in order to allow catching errors of type E_WARNING, E_NOTICE etc.,
     * which are not catchable via a try/catch statement.
     * It will fail the current test from which it is run,
     * giving a descriptive message.
     *
     * This can come in handy to make tests for making sure that such
     * errors are not triggered by the code.
     */
    protected function catchAllErrors() {
        $this->errorHandlerChanged = true;
        set_error_handler($this->catchAllErrorHandler);
    }

    protected function getErrorType($typeNumber) {
        $errorConstants = [
            1 => 'E_ERROR',
            2 => 'E_WARNING',
            4 => 'E_PARSE',
            8 => 'E_NOTICE',
            16 => 'E_CORE_ERROR',
            32 => 'E_CORE_WARNING',
            64 => 'E_COMPILE_ERROR',
            128 => 'E_COMPILE_WARNING',
            256 => 'E_USER_ERROR',
            512 => 'E_USER_WARNING',
            1024 => 'E_USER_NOTICE',
            2048 => 'E_STRICT',
            4096 => 'E_RECOVERABLE_ERROR',
            8192 => 'E_DEPRECATED',
            16384 => 'E_USER_DEPRECATED',
            32767 => 'E_ALL'
        ];
        return $errorConstants[$typeNumber];
    }

    protected function getDocumentInstance()
    {
        return new Document();
    }

    protected function getElementInstance($value)
    {
        return new Element($value);
    }

    protected function getParserInstance()
    {
        return new Parser();
    }
}
