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

namespace PHPUnitTests\Integration;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\Pages;
use Smalot\PdfParser\PDFObject;

/**
 * General Document related tests.
 */
class DocumentTest extends TestCase
{
    protected function getDocumentInstance(): Document
    {
        return new Document();
    }

    protected function getPDFObjectInstance(Document $document, ?Header $header = null): PDFObject
    {
        return new PDFObject($document, $header);
    }

    protected function getPageInstance(Document $document, Header $header): PDFObject
    {
        return new Page($document, $header);
    }

    protected function getPagesInstance(Document $document, Header $header): PDFObject
    {
        return new Pages($document, $header);
    }

    public function testSetObjects(): void
    {
        $document = $this->getDocumentInstance();
        $object = $this->getPDFObjectInstance($document);

        // Obj #1 is missing
        $this->assertNull($document->getObjectById(1));
        $document->setObjects([1 => $object]);

        // Obj #1 exists
        $this->assertTrue($document->getObjectById(1) instanceof PDFObject);

        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object = $this->getPDFObjectInstance($document, $header);
        $document->setObjects([2 => $object]);

        // Obj #1 is missing
        $this->assertNull($document->getObjectById(1));

        // Obj #2 exists
        $this->assertTrue($document->getObjectById(2) instanceof PDFObject);
    }

    public function testGetObjects(): void
    {
        $document = $this->getDocumentInstance();
        $object1 = $this->getPDFObjectInstance($document);
        $content = '<</Type/Page>>unparsed content';
        $header = Header::parse($content, $document);

        $object2 = $this->getPageInstance($document, $header);
        $document->setObjects([1 => $object1, 2 => $object2]);

        $objects = $document->getObjects();
        $this->assertEquals(2, \count($objects));
        $this->assertTrue($objects[1] instanceof PDFObject);
        $this->assertTrue($objects[2] instanceof PDFObject);
        $this->assertTrue($objects[2] instanceof Page);
    }

    public function testDictionary(): void
    {
        $document = $this->getDocumentInstance();
        $objects = $document->getDictionary();
        $this->assertEquals(0, \count($objects));
        $object1 = $this->getPDFObjectInstance($document);

        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object2 = $this->getPageInstance($document, $header);
        $document->setObjects([1 => $object1, 2 => $object2]);

        $objects = $document->getDictionary();
        $this->assertEquals(1, \count($objects));
        $this->assertEquals(1, \count($objects['Page']['all']));
        $this->assertEquals($object2, $objects['Page']['all'][2]);
    }

    public function testGetObjectsByType(): void
    {
        $document = $this->getDocumentInstance();
        $object1 = $this->getPDFObjectInstance($document);
        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object2 = $this->getPageInstance($document, $header);
        $document->setObjects([1 => $object1, 2 => $object2]);

        $objects = $document->getObjectsByType('Page');
        $this->assertEquals(1, \count($objects));
        $this->assertTrue($objects[2] instanceof PDFObject);
        $this->assertTrue($objects[2] instanceof Page);
    }

    public function testGetPages(): void
    {
        $document = $this->getDocumentInstance();

        // Listing pages from type Page
        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object1 = $this->getPageInstance($document, $header);
        $header = Header::parse($content, $document);
        $object2 = $this->getPageInstance($document, $header);
        $document->setObjects([1 => $object1, 2 => $object2]);
        $pages = $document->getPages();

        $this->assertEquals(2, \count($pages));
        $this->assertTrue($pages[0] instanceof Page);
        $this->assertTrue($pages[1] instanceof Page);

        // Listing pages from type Pages (kids)
        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object1 = $this->getPageInstance($document, $header);
        $header = Header::parse($content, $document);
        $object2 = $this->getPageInstance($document, $header);
        $header = Header::parse($content, $document);
        $object3 = $this->getPageInstance($document, $header);

        $content = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header = Header::parse($content, $document);
        $object4 = $this->getPagesInstance($document, $header);

        $content = '<</Type/Pages/Kids[3 0 R]>>';
        $header = Header::parse($content, $document);
        $object5 = $this->getPagesInstance($document, $header);

        $document->setObjects([
            '1_0' => $object1,
            '2_0' => $object2,
            '3_0' => $object3,
            '4_0' => $object4,
            '5_0' => $object5,
        ]);
        $pages = $document->getPages();

        $this->assertEquals(3, \count($pages));
        $this->assertTrue($pages[0] instanceof Page);
        $this->assertTrue($pages[1] instanceof Page);
        $this->assertTrue($pages[2] instanceof Page);

        // Listing pages from type Catalog
        $content = '<</Type/Page>>';
        $header = Header::parse($content, $document);
        $object1 = $this->getPageInstance($document, $header);
        $header = Header::parse($content, $document);
        $object2 = $this->getPageInstance($document, $header);
        $header = Header::parse($content, $document);
        $object3 = $this->getPageInstance($document, $header);
        $content = '<</Type/Pages/Kids[1 0 R 2 0 R]>>';
        $header = Header::parse($content, $document);
        $object4 = $this->getPagesInstance($document, $header);
        $content = '<</Type/Pages/Kids[4 0 R 3 0 R]>>';
        $header = Header::parse($content, $document);
        $object5 = $this->getPagesInstance($document, $header);
        $content = '<</Type/Catalog/Pages 5 0 R >>';
        $header = Header::parse($content, $document);
        $object6 = $this->getPagesInstance($document, $header);
        $document->setObjects(
            [
                '1_0' => $object1,
                '2_0' => $object2,
                '3_0' => $object3,
                '4_0' => $object4,
                '5_0' => $object5,
                '6_0' => $object6,
            ]
        );
        $pages = $document->getPages();
        $this->assertEquals(3, \count($pages));
        $this->assertTrue($pages[0] instanceof Page);
        $this->assertTrue($pages[1] instanceof Page);
        $this->assertTrue($pages[2] instanceof Page);
    }

    public function testGetPagesMissingCatalog(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing catalog.');

        // Missing catalog
        $document = $this->getDocumentInstance();
        $document->getPages();
    }

    /**
     * @see https://github.com/smalot/pdfparser/issues/721
     */
    public function testExtractXMPMetadataIssue721(): void
    {
        $document = $this->getDocumentInstance();

        // Check that XMP metadata is parsed even if missing a dc:format tag
        $content = '<?xpacket begin="ï»¿" id="W5M0MpCehiHzreSzNTczkc9d"?>
<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.6-c015 84.159810, 2016/09/10-02:41:30">
   <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
      <rdf:Description>
         <dc:creator>
            <rdf:Seq>
               <rdf:li>PdfParser</rdf:li>
            </rdf:Seq>
         </dc:creator>
         <xmp:CreateDate>2018-02-07T11:51:44-05:00</xmp:CreateDate>
         <xmp:ModifyDate>2019-10-23T09:56:01-04:00</xmp:ModifyDate>
      </rdf:Description>
   </rdf:RDF>
</x:xmpmeta>';

        $document->extractXMPMetadata($content);
        $document->init();
        $details = $document->getDetails();

        $this->assertEquals(4, \count($details));
        $this->assertEquals('PdfParser', $details['dc:creator']);
        $this->assertEquals('2019-10-23T09:56:01-04:00', $details['xmp:modifydate']);
    }
}
