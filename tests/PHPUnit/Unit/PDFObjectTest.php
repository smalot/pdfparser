<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;
use Smalot\PdfParser\XObject\Form;
use Smalot\PdfParser\XObject\Image;

class PDFObjectTest extends TestCase
{
    public function testGetTextOnNullPage(): void
    {
        static::assertSame(' ', (new PDFObject(new Document()))->getText());
    }

    public function testGetTextOnPageWithoutContent(): void
    {
        $document = new Document();

        static::assertSame(' ', (new PDFObject($document, null, null))->getText(new Page($document)));
    }

    public function testTextArrayObjects(): void
    {
        $document = new Document();
        $document->init();

        $image = new Image($document);
        $form = new Form($document);
        $xObject = new PDFObject($document);

        $header1 = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Im0' => $image,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Im0 Do', $document)], $document),
        ]);
        $page1 = new Page($document, $header1);

        $header2 = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $form,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page2 = new Page($document, $header2);

        $header3 = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Ps0' => $xObject,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Ps0 Do', $document)], $document),
        ]);
        $page3 = new Page($document, $header3);

        // Page 1 contains an image, which should not appear in the text array.
        self::assertSame([], $page1->getTextArray());

        // Page 2 contains a form, which should not appear in the text array.
        self::assertSame([], $page2->getTextArray());

        // Page 3 contains a non-image object, which should appear in the text array.
        self::assertSame([' '], $page3->getTextArray());
    }
}
