<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
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

namespace Tests\Smalot\PdfParser\Integration;

use Exception;
use Smalot\PdfParser\Parser;
use Test\Smalot\PdfParser\TestCase;

class ParserTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new Parser();
    }

    public function testParseFile()
    {
        $directory = $this->rootDir.'/samples/bugs';

        if (is_dir($directory)) {
            $files = scandir($directory);

            foreach ($files as $file) {
                if (preg_match('/^.*\.pdf$/i', $file)) {
                    try {
                        $document = $this->fixture->parseFile($directory.'/'.$file);
                        $pages = $document->getPages();
                        $this->assertTrue(0 < \count($pages));

                        foreach ($pages as $page) {
                            $content = $page->getText();
                            $this->assertTrue(0 < \strlen($content));
                        }
                    } catch (Exception $e) {
                        if (
                            'Secured pdf file are currently not supported.' !== $e->getMessage()
                            && 0 != strpos($e->getMessage(), 'TCPDF_PARSER')
                        ) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    /**
     * Parsing certain PDFs may lead to following notices:
     *
     *      Notice: Trying to access array offset on value of type int
     *
     * and to an exception:
     *
     *      Missing catalog.
     *
     * @see https://github.com/smalot/pdfparser/issues/267
     */
    public function testIssue267()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing catalog.');

        $filename = $this->rootDir.'/samples/bugs/Issue267_array_access_on_int.pdf';

        $document = $this->fixture->parseFile($filename);

        // triggers the exception
        $document->getPages();
    }
}
