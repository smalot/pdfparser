<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
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
 *
 */

namespace Smalot\PdfParser\Tests\Units\Element;

use mageekguy\atoum;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;

/**
 * Class ElementArray
 *
 * @package Smalot\PdfParser\Tests\Units\Element
 */
class ElementArray extends atoum\test
{
    public function testParse()
    {
        $document = new \Smalot\PdfParser\Document(array());

        // Skipped.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('ABC', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' / [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' 0 [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" 0 \n [ 4 2 ] ", $document, $offset);
        $this->assert->boolean($element)->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(0);

        // Valid.
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ] ', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(8);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse('[ 4 2 ]', $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(7);
        $offset  = 0;
        $element = \Smalot\PdfParser\Element\ElementArray::parse(" \n [ 4 2 ] ", $document, $offset);
        $this->assert->boolean($element->contains(4))->isEqualTo(true);
        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
        $this->assert->integer($offset)->isEqualTo(10);
    }

    public function testGetContent()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $content = $element->getContent();
        $this->assert->array($content)->hasSize(2);
    }

    public function testContains()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));

        $this->assert->boolean($element->contains(2))->isEqualTo(true);
        $this->assert->boolean($element->contains(8))->isEqualTo(false);
    }

    public function testResolveXRef()
    {
        // Document with text.
        $filename = __DIR__ . '/../../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser   = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $object   = $document->getObjectById('3_0');
        $kids     = $object->get('Kids');

        $this->assert->object($kids)->isInstanceOf('\Smalot\PdfParser\Element\ElementArray');
        $this->assert->array($kids->getContent())->hasSize(1);

        $pages = $kids->getContent();
        $this->assert->object(reset($pages))->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testGetDetails()
    {
//        // Document with text.
//        $filename = __DIR__ . '/../../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
//        $parser   = new \Smalot\PdfParser\Parser();
//        $document = $parser->parseFile($filename);
//        $object   = $document->getObjectById('3_0');
//        /** @var \Smalot\PdfParser\Element\ElementArray $kids */
//        $kids    = $object->get('Kids');
//        $details = $kids->getDetails();
//
//        $this->assert->array($details)->hasSize(1);
//        $this->assert->string($details[0]['Type'])->isEqualTo('Page');

        $document          = new Document();
        $content           = '<</Type/Page/Types[8]/Sizes[1 2 3 4 5 <</Subtype/XObject>> [8 [9 <</FontSize 10>>]]]>>';
        $details_reference = array(
            'Type'  => 'Page',
            'Types' => array(
                8,
            ),
            'Sizes' => array(
                1,
                2,
                3,
                4,
                5,
                array(
                    'Subtype' => 'XObject',
                ),
                array(
                    8,
                    array(
                        9,
                        array(
                            'FontSize' => 10,
                        ),
                    ),
                ),
            ),
        );
        $header            = Header::parse($content, $document);
        $details           = $header->getDetails();

        $this->assert->array($details)->hasSize(3);
        $this->assert->array($details)->isEqualTo($details_reference);
    }

    public function test__toString()
    {
        $val_4   = new \Smalot\PdfParser\Element\ElementNumeric('4');
        $val_2   = new \Smalot\PdfParser\Element\ElementNumeric('2');
        $element = new \Smalot\PdfParser\Element\ElementArray(array($val_4, $val_2));
        $this->assert->castToString($element)->isEqualTo('4,2');

        $document = new \Smalot\PdfParser\Document(array());
        $element  = \Smalot\PdfParser\Element\ElementArray::parse(' [ 4 2 ]', $document);
        $this->assert->castToString($element)->isEqualTo('4,2');
    }
}
