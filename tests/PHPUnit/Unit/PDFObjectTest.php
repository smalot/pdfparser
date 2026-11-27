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
    protected function setUp(): void
    {
        parent::setUp();

        // The recursion stack is static and only emptied by Page::getText().
        // An object, whose hash equals an entry of the stack, provides no text.
        PDFObject::$recursionStack = [];
    }

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
     * Kerned TJ arrays split a word into many small string operands, some of
     * them more than once ("l", "o"). Each operand ends up at its position.
     *
     * @see https://github.com/smalot/pdfparser/issues/712
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=258 ISO 32000-1:2008, 9.4.3, Table 109 (TJ)
     */
    public function testGetTextArrayReassemblesKernedTjArray(): void
    {
        $content = 'BT /F1 12 Tf 10 10 Td '
            .'[(H)10(e)-5(l)3(l)20(o)-40( )30(W)5(o)-3(r)8(l)2(d)]TJ ET';

        self::assertSame(['Hello World '], $this->getTextArrayOfFormContent($content));
    }

    /**
     * The property list of a marked-content sequence contains a string itself.
     * The text which follows the BDC operator is extracted nevertheless.
     *
     * @see https://github.com/smalot/pdfparser/issues/712
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=561 ISO 32000-1:2008, 14.6.1, Table 320 (BDC, EMC)
     */
    public function testGetTextArrayRestoresMarkedContentDictionary(): void
    {
        $content = '/OC << /MCID 0 /Foo (bar) >> BDC '
            .'BT /F1 12 Tf 10 10 Td (Hello) Tj ET EMC';

        self::assertSame(['Hello '], $this->getTextArrayOfFormContent($content));
    }

    /**
     * A literal string may contain balanced pairs of parentheses, which are
     * part of the string.
     *
     * @see https://github.com/smalot/pdfparser/issues/712
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=23 ISO 32000-1:2008, 7.3.4.2 (literal strings)
     */
    public function testGetTextArrayKeepsBalancedParenthesesInsideString(): void
    {
        $content = 'BT /F1 12 Tf 10 10 Td (a(b)c) Tj ET';

        self::assertSame(['a(b)c '], $this->getTextArrayOfFormContent($content));
    }

    /**
     * Pairs of parentheses may be nested, and escaped parentheses don't
     * count. The escape characters are removed from the text.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=23 ISO 32000-1:2008, 7.3.4.2 (literal strings)
     */
    public function testGetTextArrayKeepsNestedParenthesesInsideString(): void
    {
        self::assertSame(
            ['a(b(c)d)e '],
            $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td (a(b(c)d)e) Tj ET')
        );

        PDFObject::$recursionStack = [];
        self::assertSame(
            ['(((()))) '],
            $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td ((((())))) Tj ET')
        );

        PDFObject::$recursionStack = [];
        self::assertSame(
            ['a((b))c '],
            $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td (a\((b)\)c) Tj ET')
        );
    }

    /**
     * Text of an unterminated string is extracted, as are the strings in
     * front of it.
     */
    public function testGetTextArrayWithUnterminatedString(): void
    {
        self::assertSame(
            ['okbroken '],
            $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td (ok) Tj (broken Tj ET')
        );
    }

    /**
     * Each marked-content sequence provides its text, also if its property
     * list contains a string.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=561 ISO 32000-1:2008, 14.6.1, Table 320 (BDC, EMC)
     */
    public function testGetTextArrayOfConsecutiveMarkedContentSequences(): void
    {
        $content = '/P <</MCID 0 /T (t)>> BDC BT /F1 12 Tf 10 10 Td (A) Tj ET EMC '
            .'/P <</MCID 1>> BDC BT /F1 12 Tf 10 30 Td (B) Tj ET EMC';

        self::assertSame(["A\nB "], $this->getTextArrayOfFormContent($content));
    }

    /**
     * Data and dictionary of an inline image don't affect the text behind
     * it, neither do parentheses inside of them.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=222 ISO 32000-1:2008, 8.9.7 (inline images)
     */
    public function testGetTextArrayBehindInlineImage(): void
    {
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('q BI /W 1 /H 1 ID (( EI Q BT /F1 12 Tf 10 10 Td (a) Tj ET')
        );

        PDFObject::$recursionStack = [];
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('q BI /W 1 /H 1 /X (a(b)c) ID x EI Q BT /F1 12 Tf 10 10 Td (a) Tj ET')
        );
    }

    /**
     * A dictionary with an unbalanced closing bracket doesn't prevent text
     * extraction, neither for an inline image nor for a marked-content
     * sequence. It made parseDictionary() throw a TypeError.
     */
    public function testGetTextArrayWithUnbalancedClosingBracketInDictionary(): void
    {
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('q BI /W 1 /H 1 /X ] ID x EI Q BT /F1 12 Tf 10 10 Td (a) Tj ET')
        );

        PDFObject::$recursionStack = [];
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('/P <</A ]>> BDC BT /F1 12 Tf 10 10 Td (a) Tj ET EMC')
        );

        PDFObject::$recursionStack = [];
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('/P <</A 1>> >> BDC BT /F1 12 Tf 10 10 Td (a) Tj ET EMC')
        );
    }

    /**
     * A Q operator without preceding q is ignored, the graphics state stays
     * as it is. Restoring from the empty stack set the font to null, which
     * ended in a TypeError.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=135 ISO 32000-1:2008, 8.4.4, Table 57 (q, Q)
     */
    public function testGetTextArrayWithUnbalancedRestoreGraphicsState(): void
    {
        self::assertSame(
            ['a '],
            $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td Q (a) Tj ET')
        );

        // Same text as without the superfluous Q operators
        $content = 'BT /F1 12 Tf 10 10 Td (a) Tj ET q 1 0 0 1 50 50 cm BT /F1 12 Tf 10 10 Td (b) Tj ET Q';

        PDFObject::$recursionStack = [];
        $expected = $this->getTextArrayOfFormContent($content);

        PDFObject::$recursionStack = [];
        self::assertSame(
            $expected,
            $this->getTextArrayOfFormContent('Q Q '.$content.' Q')
        );
    }

    /**
     * The size of an inline image doesn't affect the text behind it. A
     * regular expression built from more than 64 KB of image data could
     * not be compiled, the text of the whole content stream got lost.
     */
    public function testGetTextArrayBehindLargeInlineImage(): void
    {
        $content = 'q BI /W 1 /H 1 ID '.str_repeat('x', 100000).' EI Q BT /F1 12 Tf 10 10 Td (a) Tj ET';

        self::assertSame(['a '], $this->getTextArrayOfFormContent($content));
    }

    /**
     * The size of a marked-content dictionary doesn't affect the text of the
     * sequence. A regular expression built from a dictionary of more than
     * 64 KB could not be compiled, the text of the whole content stream got
     * lost.
     */
    public function testGetTextArrayOfMarkedContentWithLargeDictionary(): void
    {
        $content = '/P <</A /'.str_repeat('x', 100000).'>> BDC BT /F1 12 Tf 10 10 Td (a) Tj ET EMC';

        self::assertSame(['a '], $this->getTextArrayOfFormContent($content));
    }

    /**
     * Provides the text array of a page, whose content stream invokes a form
     * XObject with the given content.
     */
    private function getTextArrayOfFormContent(string $content): array
    {
        $document = new Document();
        $document->init();

        $form = new Form($document, null, $content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header([
                    'Fr0' => $form,
                ]),
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);

        return (new Page($document, $header))->getTextArray();
    }
}
