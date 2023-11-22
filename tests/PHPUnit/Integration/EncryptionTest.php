<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Alastair Irvine <alastair@plug.org.au>
 *
 * @date    2023-11-22
 *
 * @license LGPLv3
 *
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

namespace PHPUnitTests\Integration;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class EncryptionTest extends TestCase
{
    public function testNoIgnoreEncryption(): void
    {
        $parser = new Parser();

        $filename = $this->rootDir.'/samples/not_really_encrypted.pdf';
        $threw = false;
        try {
            $document = $parser->parseFile($filename);
        } catch (\Exception $e) {
            $threw = true;
        }
        $this->assertTrue($threw);
    }

    public function testIgnoreEncryption(): void
    {
        $config = new Config();
        $config->setIgnoreEncryption(true);
        $parser = new Parser([], $config);

        $filename = $this->rootDir.'/samples/not_really_encrypted.pdf';
        $document = $parser->parseFile($filename);
        $this->assertTrue(true);
    }
}
