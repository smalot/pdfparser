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

namespace Tests\Smalot\PdfParser\Integration\RawData;

use Smalot\PdfParser\RawData\RawDataParser;
use Tests\Smalot\PdfParser\TestCase;

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
    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RawDataParserHelper();
    }

    /**
     * Tests buggy behavior of getRawObject.
     *
     * When PDF has corrupted xref table getRawObject may run into an infinite loop.
     *
     * @see https://github.com/smalot/pdfparser/issues/372
     * @see https://github.com/smalot/pdfparser/pull/377
     */
    public function testGetRawObjectIssue372()
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
    }
}
