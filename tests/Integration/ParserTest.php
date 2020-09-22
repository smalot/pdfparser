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
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\XObject\Image;
use Tests\Smalot\PdfParser\TestCase;

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
     * Test that issue related pdf can now be parsed
     *
     * @see https://github.com/smalot/pdfparser/issues/267
     */
    public function testIssue267()
    {
        $filename = $this->rootDir.'/samples/bugs/Issue267_array_access_on_int.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertEquals(Image::class, \get_class($document->getObjectById('128_0')));
        $this->assertStringContainsString('4 von 4', $document->getText());
    }

    public function docProvider()
    {
        return [
            'adobe-compressed-pdf16.pdf' => ['adobe-compressed-pdf16.pdf'],
            'adobe-converted-pdf16.pdf' => ['adobe-converted-pdf16.pdf'],
            'google-docs-export-pdf15.pdf' => ['google-docs-export-pdf15.pdf'],
        ];
    }

    /**
     * @dataProvider docProvider
     */
    public function testParserForDifferentSource($testDoc)
    {
        $filename = $this->rootDir."/samples/$testDoc";

        /** @var Document $document */
        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString('Test document', $document->getText());
        $this->assertStringContainsString('Test mono', $document->getText());

        $i = 0;
        foreach ($document->getObjects() as $object) {
            if (Image::class === \get_class($object)) {
                ++$i;
            }
        }
        $this->assertEquals(1, $i, 'Asserting has exactly one image');
    }

    /**
     * @see https://github.com/smalot/pdfparser/issues/201
     */
    public function testIssue201()
    {
        $filename = $this->rootDir.'/samples/bugs/issue201.pdf';

        /** @var Document $document */
        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString('The pdf995 suite of products', $document->getText());
    }
}
