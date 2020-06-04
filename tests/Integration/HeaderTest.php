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

use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;
use Test\Smalot\PdfParser\TestCase;

/**
 * Class Header
 */
class HeaderTest extends TestCase
{
    public function testParse()
    {
        $document = $this->getDocumentInstance();

        $content = '<</Type/Page/SubType/Text>>foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);

        $this->assertTrue($header instanceof Header);
        $this->assertEquals(27, $position);
        $this->assertEquals(2, \count($header->getElements()));

        // No header to parse
        $this->assertEquals('Page', (string) $header->get('Type'));
        $content = 'foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);

        $this->assertTrue($header instanceof Header);
        $this->assertEquals(0, $position);
        $this->assertEquals(0, \count($header->getElements()));

        $position = 0;
        $content = "<</CreationDate(D:20100309184803+01'00')/Author(Utilisateur)/Creator(PScript5.dll Version 5.2.2)/Producer(Acrobat Distiller 7.0.5 \(Windows\))/ModDate(D:20100310104810+01'00')/Title(Microsoft Word - CLEMI.docx)>>";
        Header::parse($content, $document, $position);
        $this->assertEquals(212, $position);

        $position = 0;
        $content = '[5 0 R ] foo';
        $header = Header::parse($content, $document, $position);
        $this->assertEquals(8, $position);
        $this->assertEquals(1, \count($header->getElements()));
    }

    public function testGetElements()
    {
        $document = $this->getDocumentInstance();

        $content = '<</Type/Page/Subtype/Text>>foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);

        $elements = $header->getElements();
        $this->assertEquals(2, \count($elements));
        $this->assertTrue(current($elements) instanceof ElementName);

        $types = $header->getElementTypes();
        $this->assertTrue(\is_array($types));
        $this->assertEquals(ElementName::class, $types['Type']);
        $this->assertEquals(ElementName::class, $types['Subtype']);
    }

    public function testHas()
    {
        $document = $this->getDocumentInstance();

        $content = '<</Type/Page/SubType/Text/Font 5 0 R>>foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);

        $this->assertTrue($header->has('Type'));
        $this->assertTrue($header->has('SubType'));
        $this->assertTrue($header->has('Font'));
        $this->assertFalse($header->has('Text'));
    }

    public function testGet()
    {
        $document = $this->getDocumentInstance();

        $content = '<</Type/Page/SubType/Text/Font 5 0 R/Resources 8 0 R>>foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);
        $object = new Page($document, $header);
        $document->setObjects(['5_0' => $object]);

        $this->assertTrue($header->get('Type') instanceof ElementName);
        $this->assertTrue($header->get('SubType') instanceof ElementName);
        $this->assertTrue($header->get('Font') instanceof Page);
        $this->assertTrue($header->get('Image') instanceof ElementMissing);
        $this->assertTrue($header->get('Resources') instanceof ElementMissing);
    }

    public function testResolveXRef()
    {
        $document = $this->getDocumentInstance();
        $content = '<</Type/Page/SubType/Text/Font 5 0 R/Resources 8 0 R>>foo';
        $position = 0;
        $header = Header::parse($content, $document, $position);
        $object = new Page($document, $header);
        $document->setObjects(['5_0' => $object]);

        $this->assertTrue($header->get('Font') instanceof PDFObject);

        $header = $header->get('Resources');
        $this->assertTrue($header instanceof ElementMissing);
    }
}
