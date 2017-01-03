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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Document
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Document extends atoum\test
{
    public function testSetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object   = new \Smalot\PdfParser\Object($document);
        // Obj #1 is missing
        $this->assert->variable($document->getObjectById(1))->isNull();
        $document->setObjects(array(1 => $object));
        // Obj #1 exists
        $this->assert->object($document->getObjectById(1))->isInstanceOf('\Smalot\PdfParser\Object');

        $content = '<</Type/Page>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object  = new \Smalot\PdfParser\Object($document, $header);
        $document->setObjects(array(2 => $object));
        // Obj #1 is missing
        $this->assert->assert->variable($document->getObjectById(1))->isNull();
        // Obj #2 exists
        $this->assert->object($document->getObjectById(2))->isInstanceOf('\Smalot\PdfParser\Object');
    }

    public function testGetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>unparsed content';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);

        $object2 = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));

        $this->assert->integer(count($objects = $document->getObjects()))->isEqualTo(2);
        $this->assert->object($objects[1])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testDictionary()
    {
        $document = new \Smalot\PdfParser\Document();
        $this->assert->integer(count($objects = $document->getDictionary()))->isEqualTo(0);
        $object1 = new \Smalot\PdfParser\Object($document);
        $content = '<</Type/Page>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object2 = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->assert->integer(count($objects = $document->getDictionary()))->isEqualTo(1);
        $this->assert->integer(count($objects['Page']))->isEqualTo(1);
        $this->assert->integer($objects['Page'][2])->isEqualTo(2);
    }

    public function testGetObjectsByType()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->assert->integer(count($objects = $document->getObjectsByType('Page')))->isEqualTo(1);
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Object');
        $this->assert->object($objects[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }

    public function testGetPages()
    {
        // Missing catalog
        $document = new \Smalot\PdfParser\Document();
        try {
            $pages = $document->getPages();
            $this->assert->boolean($pages)->isEqualTo(false);
        } catch (\Exception $e) {
            $this->assert->object($e)->isInstanceOf('\Exception');
        }

        // Listing pages from type Page
        $content = '<</Type/Page>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object1 = new \Smalot\PdfParser\Page($document, $header);
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object2 = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(2);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');

        // Listing pages from type Pages (kids)
        $content = '<</Type/Page>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object1 = new \Smalot\PdfParser\Page($document, $header);
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object2 = new \Smalot\PdfParser\Page($document, $header);
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object3 = new \Smalot\PdfParser\Page($document, $header);
        $content = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object4 = new \Smalot\PdfParser\Pages($document, $header);
        $content = '<</Type/Pages/Kids[3 0 R]>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object5 = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(
            array('1_0' => $object1, '2_0' => $object2, '3_0' => $object3, '4_0' => $object4, '5_0' => $object5)
        );
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(3);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[2])->isInstanceOf('\Smalot\PdfParser\Page');

        // Listing pages from type Catalog
        $content = '<</Type/Page>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object1 = new \Smalot\PdfParser\Page($document, $header);
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object2 = new \Smalot\PdfParser\Page($document, $header);
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object3 = new \Smalot\PdfParser\Page($document, $header);
        $content = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object4 = new \Smalot\PdfParser\Pages($document, $header);
        $content = '<</Type/Pages/Kids[4 0 R 3 0 R]>>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object5 = new \Smalot\PdfParser\Pages($document, $header);
        $content = '<</Type/Catalog/Pages 5 0 R >>';
        $header  = \Smalot\PdfParser\Header::parse($content, $document);
        $object6 = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(
            array(
                '1_0' => $object1,
                '2_0' => $object2,
                '3_0' => $object3,
                '4_0' => $object4,
                '5_0' => $object5,
                '6_0' => $object6
            )
        );
        $pages = $document->getPages();
        $this->assert->integer(count($pages))->isEqualTo(3);
        $this->assert->object($pages[0])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[1])->isInstanceOf('\Smalot\PdfParser\Page');
        $this->assert->object($pages[2])->isInstanceOf('\Smalot\PdfParser\Page');
    }
}
