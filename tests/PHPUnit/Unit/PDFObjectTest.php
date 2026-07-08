<?php

declare(strict_types=1);

namespace PHPUnitTests\Unit;

use PHPUnitTests\TestCase;
use Smalot\PdfParser\Config;
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

        $config = new Config();
        $image = new Image($document);
        $formNoText = new Form($document);
        $formWithText = new Form($document, null, 'BT /F1 12 Tf 10 10 Td (Form text) Tj ET', $config);
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
                    'Fr0' => $formNoText,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page2 = new Page($document, $header2);

        $header3 = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $formWithText,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page3 = new Page($document, $header3);

        $header4 = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Ps0' => $xObject,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Ps0 Do', $document)], $document),
        ]);
        $page4 = new Page($document, $header4);

        // Page 1 contains an image, which should not appear in the text array.
        self::assertSame([], $page1->getTextArray());

        // Page 2 contains a form that contains no text, which should not appear
        // in the text array.
        self::assertSame([], $page2->getTextArray());

        // Page 3 contains a form that contains text, which should appear in the
        // text array.
        self::assertSame(['Form text '], $page3->getTextArray());

        // Page 4 contains a non-image object, which should appear in the text
        // array.
        self::assertSame([' '], $page4->getTextArray());
    }

    /**
     * Kerned TJ arrays split a word into many small string operands. Make sure
     * they end up in the right order again (issue #712).
     */
    public function testGetTextArrayReassemblesKernedTjArray(): void
    {
        $document = new Document();
        $document->init();

        $content = 'BT /F1 12 Tf 10 10 Td '
            . '[(H)10(e)-5(l)3(l)20(o)-40( )30(W)5(o)-3(r)8(l)2(d)]TJ ET';

        $form = new Form($document, null, $content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $form,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page = new Page($document, $header);

        self::assertSame(['Hello World '], $page->getTextArray());
    }

    /**
     * A << ... >> BDC dictionary around a text block must not swallow the
     * text that follows it (issue #712).
     */
    public function testGetTextArrayRestoresMarkedContentDictionary(): void
    {
        $document = new Document();
        $document->init();

        $content = '/OC << /MCID 0 /Foo (bar) >> BDC '
            . 'BT /F1 12 Tf 10 10 Td (Hello) Tj ET EMC';

        $form = new Form($document, null, $content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $form,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page = new Page($document, $header);

        self::assertSame(['Hello '], $page->getTextArray());
    }

    /**
     * A string can hold balanced unescaped parentheses; check they survive
     * extraction (issue #712).
     */
    public function testGetTextArrayKeepsBalancedParenthesesInsideString(): void
    {
        $document = new Document();
        $document->init();

        $content = 'BT /F1 12 Tf 10 10 Td (a(b)c) Tj ET';

        $form = new Form($document, null, $content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $form,
                ])
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);
        $page = new Page($document, $header);

        self::assertSame(['a(b)c '], $page->getTextArray());
    }
}
