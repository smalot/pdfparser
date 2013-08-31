<?php

/**
 * @file
 * This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Document
 * @package Smalot\PdfParser\Tests\Units
 */
class Document extends atoum\test
{
    public function testSetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object   = new \Smalot\PdfParser\Object($document);
        $this->boolean($document->getObjectById(1) instanceof \Smalot\PdfParser\Object)->isEqualTo(false);
        $document->setObjects(array(1 => $object));
        $this->boolean($document->getObjectById(1) instanceof \Smalot\PdfParser\Object)->isEqualTo(true);

        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object   = new \Smalot\PdfParser\Object($document, $header);
        $document->setObjects(array(2 => $object));
        $this->boolean($document->getObjectById(1) instanceof \Smalot\PdfParser\Object)->isEqualTo(false);
        $this->boolean($document->getObjectById(2) instanceof \Smalot\PdfParser\Object)->isEqualTo(true);
    }

    public function testGetObjects()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->integer(count($objects = $document->getObjects()))->isEqualTo(2);
        $this->boolean($objects[1] instanceof \Smalot\PdfParser\Object)->isEqualTo(true);
        $this->boolean($objects[1] instanceof \Smalot\PdfParser\Page)->isEqualTo(false);
        $this->boolean($objects[2] instanceof \Smalot\PdfParser\Object)->isEqualTo(true);
        $this->boolean($objects[2] instanceof \Smalot\PdfParser\Page)->isEqualTo(true);
    }

    public function testDictionary()
    {
        $document = new \Smalot\PdfParser\Document();
        $this->integer(count($objects = $document->getDictionary()))->isEqualTo(0);
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->integer(count($objects = $document->getDictionary()))->isEqualTo(1);
        $this->integer(count($objects['Page']))->isEqualTo(1);
        $this->boolean(isset($objects['Page'][2]))->isEqualTo(true);
        $this->boolean($objects['Page'][2] == 2)->isEqualTo(true);
    }

    public function testGetObjectsByType()
    {
        $document = new \Smalot\PdfParser\Document();
        $object1  = new \Smalot\PdfParser\Object($document);
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $this->integer(count($objects = $document->getObjectsByType('Page')))->isEqualTo(1);
        $this->boolean($objects[2] instanceof \Smalot\PdfParser\Object)->isEqualTo(true);
        $this->boolean($objects[2] instanceof \Smalot\PdfParser\Page)->isEqualTo(true);
    }

    public function testGetPages()
    {
        // Missing catalog
        $document = new \Smalot\PdfParser\Document();
        try {
            $pages = $document->getPages();
            $this->boolean($pages)->isEqualTo(false);
        } catch(\Exception $e) {
            $this->boolean($e instanceof \Exception)->isEqualTo(true);
        }

        // Listing pages from type Page
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2));
        $pages = $document->getPages();
        $this->integer(count($pages))->isEqualTo(2);

        // Listing pages from type Pages (kids)
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object3  = new \Smalot\PdfParser\Page($document, $header);
        $content  = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object4  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Pages/Kids[3 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object5  = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2, 3 => $object3, 4 => $object4, 5 => $object5));
        $pages = $document->getPages();
        $this->integer(count($pages))->isEqualTo(3);

        // Listing pages from type Catalog
        $content  = '<</Type/Page>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object1  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object2  = new \Smalot\PdfParser\Page($document, $header);
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object3  = new \Smalot\PdfParser\Page($document, $header);
        $content  = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object4  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Pages/Kids[4 0 R 3 0 R]>>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object5  = new \Smalot\PdfParser\Pages($document, $header);
        $content  = '<</Type/Catalog/Pages 5 0 R >>';
        $header   = \Smalot\PdfParser\Header::parse($content, $document);
        $object6  = new \Smalot\PdfParser\Pages($document, $header);
        $document->setObjects(array(1 => $object1, 2 => $object2, 3 => $object3, 4 => $object4, 5 => $object5, 6 => $object6));
        $pages = $document->getPages();
        $this->integer(count($pages))->isEqualTo(3);
    }

    public function testParseFile()
    {

    }

    public function testParseContent()
    {
        $content = <<<EOT
5 0 obj
5198
endobj
2 0 obj
<< /Type /Page /Parent 3 0 R /Resources 6 0 R /Contents 4 0 R /MediaBox [0 0 595.32 841.92]
>>
endobj
6 0 obj
<< /ProcSet [ /PDF /Text /ImageB /ImageC /ImageI ] /ColorSpace << /Cs1 13 0 R
/Cs2 14 0 R >> /Font << /F5.1 16 0 R /F1.0 7 0 R /F4.1 12 0 R /F2.1 9 0 R
/F3.0 10 0 R >> /XObject << /Im1 17 0 R >> >>
endobj

EOT;
        $document = \Smalot\PdfParser\Document::parseContent($content);
//        var_dump($document);
        $this->string($document->getObjectById(5)->getContent())->isEqualTo('5198');
        $this->castToString($document->getObjectById(2)->get('Type'))->isEqualTo('Page');
        $object6 = $document->getObjectById(6)->get('ProcSet');
        $this->boolean($object6 instanceof \Smalot\PdfParser\Element\ElementArray)->isEqualTo(true);
    }
}
