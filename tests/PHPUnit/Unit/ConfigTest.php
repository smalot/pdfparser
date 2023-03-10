<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2020-12-15
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

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Config();
    }

    /**
     * Tests setter and getter for font space limit.
     */
    public function testFontSpaceLimitSetterGetter(): void
    {
        $this->assertEquals(-50, $this->fixture->getFontSpaceLimit());

        $this->fixture->setFontSpaceLimit(1);
        $this->assertEquals(1, $this->fixture->getFontSpaceLimit());
    }

    /**
     * Tests setter and getter for horizontal offset.
     */
    public function testHorizontalOffsetSetterGetter()
    {
        $this->assertEquals(' ', $this->fixture->getHorizontalOffset());

        $this->fixture->setHorizontalOffset('	');
        $this->assertEquals('	', $this->fixture->getHorizontalOffset());
    }

    /**
     * Tests setter and getter for retaining of raw image data.
     */
    public function testRetainImageContentSetterGetter(): void
    {
        $this->assertTrue($this->fixture->getRetainImageContent());

        $this->fixture->setRetainImageContent(false);
        $this->assertFalse($this->fixture->getRetainImageContent());
    }
}
