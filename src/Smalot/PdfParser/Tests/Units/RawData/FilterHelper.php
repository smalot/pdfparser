<?php

/**
 * @file
 *          This file is part of the PdfParser library.
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

namespace Smalot\PdfParser\Tests\Units\RawData;

use Exception;
use mageekguy\atoum;
use Smalot\PdfParser\RawData\FilterHelper as FilterHelperFixture;

class FilterHelper extends atoum\test
{
    public function testDecodeFilterFlateDecode()
    {
        $fixture = new FilterHelperFixture();

        $compressed = gzcompress('Compress me', 9);
        $result = $fixture->decodeFilter('FlateDecode', $compressed);

        $this->string($result)->isEqualTo('Compress me');
    }

    /**
     * How does function behave if an empty string was given.
     */
    public function testDecodeFilterFlateDecodeEmptyString()
    {
        $fixture = new FilterHelperFixture();

        try {
            $fixture->decodeFilter('FlateDecode', '');

            /*
             * if we reach this, something went wrong.
             * we expect an exception to be thrown
             */
            $this->boolean(true)->isFalse();
        } catch (Exception $e) {
            // expected, good.
            $this->boolean(true)->isTrue();
        }
    }
}
