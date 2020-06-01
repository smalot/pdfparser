<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
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

namespace Smalot\PdfParser\Test;

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

    public function setUp()
    {
        parent::setUp();

        $this->rootDir = __DIR__.'/..';
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
