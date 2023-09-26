<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-06-01
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 *
 * @date    2017-01-03
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

namespace PHPUnitTests\Integration\RawData;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\RawData\RawDataParser;

class RawDataParserHelper extends RawDataParser
{
    /**
     * Expose protected function "getRawObject".
     */
    public function exposeGetRawObject($pdfData, $offset = 0)
    {
        return $this->getRawObject($pdfData, $offset);
    }
}

class RawDataParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RawDataParserHelper([], new Config());
    }

    /**
     * Tests buggy behavior of getRawObject.
     *
     * When PDF has corrupted xref table getRawObject may run into an infinite loop.
     *
     * @see https://github.com/smalot/pdfparser/issues/372
     * @see https://github.com/smalot/pdfparser/pull/377
     */
    public function testGetRawObjectIssue372(): void
    {
        // The following $data content is a minimal example to trigger the infinite loop
        $data = '<</Producer (eDkºãa˜þõ‚LÅòÕ�PïÙ��)©)>>';

        // calling "getRawObject" via "exposeGetRawObject" would result in an infinite loop
        // if the fix is not there.
        $result = $this->fixture->exposeGetRawObject($data);

        $this->assertEquals(
            [
                '<<',
                [
                    ['/', 'Producer', 11],
                    ['(', 'eDkºãa˜þõ‚LÅòÕ�PïÙ��', 52],
                ],
                52,
            ],
            $result
        );

        // Test that spaces after a 'stream' declaration are absorbed
        // See: https://github.com/smalot/pdfparser/issues/641
        $data = 'stream '."\n";
        $data .= 'streamdata'."\n";
        $data .= 'endstream'."\n";
        $data .= 'endobj';

        $result = $this->fixture->exposeGetRawObject($data);

        // Value 'streamdata'."\n" would be empty string without the fix
        $this->assertEquals(
            [
                'stream',
                'streamdata'."\n",
                19,
            ],
            $result
        );
    }

    /**
     * Tests buggy behavior of decodeXrefStream.
     *
     * @see https://github.com/smalot/pdfparser/issues/30
     * @see https://github.com/smalot/pdfparser/issues/192
     * @see https://github.com/smalot/pdfparser/issues/209
     * @see https://github.com/smalot/pdfparser/issues/330
     * @see https://github.com/smalot/pdfparser/issues/356
     * @see https://github.com/smalot/pdfparser/issues/373
     * @see https://github.com/smalot/pdfparser/issues/392
     * @see https://github.com/smalot/pdfparser/issues/397
     */
    public function testDecodeXrefStreamIssue356(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue356.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('Ημερήσια έκθεση επιδημιολογικής', $pages[0]->getText());
    }

    public function testDecodeObjectHeaderIssue405(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue405.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('Bug fix: PR #405', $pages[0]->getText());
    }

    /**
     * Tests buggy behavior of decodeXrefStream.
     *
     * When PDF has more than one entry in the /Index area (for example by changing
     * the document description), only the first entry is used.
     * If the fix is not used the array returned by getDetails() contains only the entry
     * with the key 'Pages'. All other entries like 'Author', 'Creator', 'Title',
     * 'Subject' (which come from the 'Info' object) are not listed, because the
     * 'Info' object gets a wrong object id during parsing the data into the xref structure.
     * So the object id listed at the /Info entry is not valid and the data of the info object
     * cannot be loaded during executing Document::buildDetails().
     *
     * @see https://github.com/smalot/pdfparser/pull/479
     */
    public function testDecodeXrefStreamIssue479(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue479.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $details = $document->getDetails();

        $this->assertArrayHasKey('Author', $details);
        $this->assertArrayHasKey('CreationDate', $details);
        $this->assertArrayHasKey('Creator', $details);
        $this->assertArrayHasKey('ModDate', $details);
        $this->assertArrayHasKey('Producer', $details);
        $this->assertArrayHasKey('Subject', $details);
        $this->assertArrayHasKey('Title', $details);
    }
}
