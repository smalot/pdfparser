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
use Smalot\PdfParser\PDFObject;

class PDFObjectTest extends TestCase
{
    public const TYPE = 't';

    public const OPERATOR = 'o';

    public const COMMAND = 'c';

    protected function getPdfObjectInstance($document): PDFObject
    {
        return new PDFObject($document);
    }

    public function testGetCommandsText(): void
    {
        $content = "BT /R14 30 Tf 0.999016 0 0 1 137.4
342.561 Tm
[(A)-168.854( BC D)-220.905(\\(E\\))20.905<20>]
TJ /R14 17.16 Tf <20> Tj
0.999014 0 0 1 336.84 319.161 Tm T* ( \x00m)Tj
/R14 20.04 Tf
ET Q
q -124.774 124.127 5.64213 5.67154 930.307 4436.95 cm
BI";

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        $offset = 0;
        $parts = [];
        foreach ($sections as $section) {
            $parts[] = $this->getPdfObjectInstance(new Document())->getCommandsText($section)[0];
        }

        $reference = [
            [
                self::TYPE => '',
                self::OPERATOR => 'BT',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 30',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'Tm',
                self::COMMAND => '0.999016 0 0 1 137.4 342.561',
            ],
            [
                self::TYPE => '[',
                self::OPERATOR => 'TJ',
                self::COMMAND => [
                    [
                        self::TYPE => '(',
                        self::OPERATOR => 'TJ',
                        self::COMMAND => 'A',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '-168.854',
                    ],
                    [
                        self::TYPE => '(',
                        self::OPERATOR => 'TJ',
                        self::COMMAND => ' BC D',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '-220.905',
                    ],
                    [
                        self::TYPE => '(',
                        self::OPERATOR => 'TJ',
                        self::COMMAND => '\\(E\\)',
                    ],
                    [
                        self::TYPE => 'n',
                        self::OPERATOR => '',
                        self::COMMAND => '20.905',
                    ],
                    [
                        self::TYPE => '<',
                        self::OPERATOR => 'TJ',
                        self::COMMAND => '20',
                    ],
                ],
            ],
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 17.16',
            ],
            [
                self::TYPE => '<',
                self::OPERATOR => 'Tj',
                self::COMMAND => '20',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'Tm',
                self::COMMAND => '0.999014 0 0 1 336.84 319.161',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'T*',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '(',
                self::OPERATOR => 'Tj',
                self::COMMAND => " \x00m",
            ],
            [
                self::TYPE => '/',
                self::OPERATOR => 'Tf',
                self::COMMAND => 'R14 20.04',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'ET',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'Q',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'q',
                self::COMMAND => '',
            ],
            [
                self::TYPE => '',
                self::OPERATOR => 'cm',
                self::COMMAND => '-124.774 124.127 5.64213 5.67154 930.307 4436.95',
            ],
        ];

        $this->assertEquals($parts, $reference);
    }

    public function testCleanContent(): void
    {
        $content = '/Shape <</MCID << /Font<8>>> BT >>BDC
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Modificatio[ns] au \\(14\\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj

[ (a)-4.5(b)6(c)8.8 ( fsdfsdfsdf[]sd) ] TD

ET
/Shape <</MCID 2 >>BDC
q
0.03 841';

        $expected = '_____________________________________
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(________________________________________________)Tj
___
(___) Tj

[_____________________________________] TD

ET
______________________
q
0.03 841';

        $cleaned = $this->getPdfObjectInstance(new Document())->cleanContent($content, '_');

        $this->assertEquals($cleaned, $expected);
    }

    public function testFormatContent(): void
    {
        $content = '/Shape <</MCID << /Font<8>>> BT >>BDC Q /CS0 cs 1 1 0  scn 1 i
/GS0 gs BT /TT0 1 Tf 0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Modificatio[ns] au \\(14\\) septembre 2009 ET 2010)Tj EMC (ABC) Tj
[ (a)-4.5(b)6(c)8.8 ( fsdfsdfsdf[]sd) ] TJ ET /Shape <</MCID 2 >>BDC q 0.03 841';

        $expected = '/Shape <</MCID << /Font<8>>> BT >>BDC
Q
/CS0 cs
1 1 0 scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc
0.0018 Tw
0 Ts
100 Tz
0 Tr
24 0 0 24 51.3 639.26025 Tm
(Modificatio[ns] au \\(14\\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj
[ (a)-4.5(b)6(c)8.8 ( fsdfsdfsdf[]sd) ] TJ
ET
/Shape <</MCID 2 >>BDC
q
0.03 841';

        // Normalize line-endings
        $expected = str_replace(["\r\n", "\n"], ["\n", "\r\n"], $expected);

        $formatContent = new \ReflectionMethod('Smalot\PdfParser\PDFObject', 'formatContent');
        $formatContent->setAccessible(true);
        $cleaned = $formatContent->invoke($this->getPdfObjectInstance(new Document()), $content);

        $this->assertEquals($expected, $cleaned);

        // Check that binary data is rejected
        $content = hex2bin('a670c89d4a324e47');

        $cleaned = $formatContent->invoke($this->getPdfObjectInstance(new Document()), $content);

        $this->assertEquals('', $cleaned);

        // See: https://github.com/smalot/pdfparser/issues/668
        $filename = $this->rootDir.'/samples/bugs/Issue668.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        // Binary check is done before a regexp that causes an error
        $this->assertStringContainsString('Marko Nestorović PR', $pages[0]->getText());

        // mb_check_encoding(..., 'UTF-8') returns true here,
        // necessitating a test for UTF-8 that's more strict
        $content = hex2bin('0101010101010101');
        $cleaned = $formatContent->invoke($this->getPdfObjectInstance(new Document()), $content);

        $this->assertEquals('', $cleaned);
    }

    /**
     * Check that escaped slashes and parentheses are accounted for,
     * formatContent would emit a PHP Warning for "regular expression
     * is too large" here without fix for issue #709
     *
     * @see https://github.com/smalot/pdfparser/issues/709
     */
    public function testFormatContentIssue709()
    {
        $formatContent = new \ReflectionMethod('Smalot\PdfParser\PDFObject', 'formatContent');
        $formatContent->setAccessible(true);

        $content = '(String \\\\\\(string)Tj '.str_repeat('(Test)Tj ', 4500);
        $cleaned = $formatContent->invoke($this->getPdfObjectInstance(new Document()), $content);

        $this->assertStringContainsString('(String \\\\\\(string)Tj'."\r\n", $cleaned);
    }

    /**
     * Check that inline image data does not corrupt the stream
     *
     * @see: https://github.com/smalot/pdfparser/issues/691
     */
    public function testFormatContentInlineImages(): void
    {
        $formatContent = new \ReflectionMethod('Smalot\PdfParser\PDFObject', 'formatContent');
        $formatContent->setAccessible(true);

        $cleaned = $formatContent->invoke(
            $this->getPdfObjectInstance(new Document()),
            'BT (This BI /W 258 /H 51 /should not trigger /as a /PDF command) TD ET q 65.30 0 0 18.00 412 707 cm BI /W 544 /H 150
/BPC 1 /IM true /F [/A85 /Fl] ID Gb"0F_$L6!$j/a\$:ma&h\'JnJJ9S?O_EA-W+%D^ClCH=FP3s5M-gStQm\'5/hc`C?<Q)riWgtEe:Po0dY_-er6$jM@#?n`E+#(sa"0Gk3&K>CqL(^pV$_-er6Ik`"-1]Q ;~> EI Q /F002 10.00 Tf 0.00 Tw 0 g'
        );

        // PdfParser should not be fooled by Q's in inline image data;
        // Only one 'Q' command should be found
        $commandQ = preg_match_all('/Q\r\n/', $cleaned);
        $this->assertEquals(1, $commandQ);

        // The 'BI' inside a string should not be interpreted as the
        // beginning of an inline image command
        $this->assertStringContainsString('(This BI /W 258 /H 51 /should not trigger /as a /PDF command) TD', $cleaned);

        $cleaned = $formatContent->invoke(
            $this->getPdfObjectInstance(new Document()),
            'BT (This BI /W 258 /H 51 /should not () \) trigger /as a /PDF command) TD (There is no ID inline image in this data) TD (Nothing but text EI should be found) TD ET'
        );

        $this->assertEquals('BT'."\r\n".
'(This BI /W 258 /H 51 /should not () \) trigger /as a /PDF command) TD'."\r\n".
'(There is no ID inline image in this data) TD'."\r\n".
'(Nothing but text EI should be found) TD'."\r\n".
'ET', $cleaned);
    }

    public function testGetSectionsText(): void
    {
        $content = '/Shape <</MCID 1 >>BDC
Q
/CS0 cs 1 1 0  scn
1 i
/GS0 gs
BT
/TT0 1 Tf
0.0007 Tc 0.0018 Tw 0  Ts 100  Tz 0 Tr 24 0 0 24 51.3 639.26025 Tm
(Mod BT atio[ns] au \\(14\\) septembre 2009 ET 2010)Tj
EMC
(ABC) Tj

[ (a)-4.5(b) 6(c)8.8 ( fsdfsdfsdf[ sd) ] TD

ET
/Shape <</MCID [BT] >>BDC BT /TT1 1.5 Tf (BT )Tj ET
q
0.03 841';

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        $this->assertEquals(
            [
                '/Shape <</MCID 1 >>BDC',
                'Q',
                'BT',
                '/TT0 1 Tf',
                '0.0007 Tc',
                '0.0018 Tw',
                '0 Ts',
                '100 Tz',
                '0 Tr',
                '24 0 0 24 51.3 639.26025 Tm',
                '(Mod BT atio[ns] au \\(14\\) septembre 2009 ET 2010)Tj',
                'EMC',
                '(ABC) Tj',
                '[ (a)-4.5(b) 6(c)8.8 ( fsdfsdfsdf[ sd) ] TD',
                'ET',
                '/Shape <</MCID [BT] >>BDC',
                'BT',
                '/TT1 1.5 Tf',
                '(BT )Tj',
                'ET',
                'q',
            ],
            $sections
        );

        // Test that a Name containing 'ET' doesn't close a 'BT' block
        // See: https://github.com/smalot/pdfparser/issues/474
        $content = 'BT
/FTxkPETkkj 8 Tf
1 0 0 1 535.55 627.4 Tm
(Hello World)TJ
ET';

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        $this->assertNotEquals('/FTxkP', $sections[0]);
        $this->assertNotEquals('/FTxkP', $sections[1]);
    }

    public function testParseDictionary(): void
    {
        $data = '<</ActualText(text)/XObject<</F2 6 0 R /F3 [/Sub /Array]>> /Array[/Parsed /Data/Actual]/Silent<>>>';

        $dictionary = $this->getPdfObjectInstance(new Document())->parseDictionary($data);

        $this->assertArrayHasKey('ActualText', $dictionary);
        $this->assertArrayHasKey('XObject', $dictionary);
        $this->assertArrayHasKey('Array', $dictionary);
        $this->assertArrayHasKey('Silent', $dictionary);

        $this->assertCount(3, $dictionary['Array']);

        $this->assertEquals('<>', $dictionary['Silent']);
    }

    /**
     * Tests that graphics position (cm) is taken into account when
     * positioning text
     *
     * @see: https://github.com/smalot/pdfparser/issues/608
     */
    public function testGraphicsPositioning(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue608.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        // The \n is not added if 'cm' commands are ignored
        $this->assertStringContainsString("Heading 1 \nLorem ipsum", $pages[0]->getText());
    }

    /**
     * Tests that ActualText text is printed for a block instead of the
     * contents of the Tj or TJ commands in the block.
     *
     * @see: https://github.com/smalot/pdfparser/issues/464
     */
    public function testActualText(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue608.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        // An ActualText command subs in the three literal characters
        // 'ffi' for the single character ligature here
        // In addition, if $last_written_position isn't used to store
        // the position to insert, \n's would be erroniously inserted
        // on either side of the 'ffi'
        $this->assertStringContainsString('efficitur', $pages[0]->getText());
    }

    /**
     * Tests for the correct decoding of an Em-dash character in
     * certain font contexts
     *
     * See: https://github.com/smalot/pdfparser/issues/585
     */
    public function testDecodeEmDash(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue585.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('слева по ходу — веревка', $pages[0]->getText());
    }

    /**
     * Tests behavior with reversed chars instruction.
     *
     * @see: https://github.com/smalot/pdfparser/issues/398
     */
    public function testReversedChars(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue398.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $pageText = $pages[0]->getText();

        $this->assertStringContainsString('שלומי טסט', $pageText);
        $this->assertStringContainsString('בנמל מספנות ישראל.', $pageText);
    }

    /**
     * Tests that a text stream with an improperly selected font code
     * page falls back to one that maps all characters.
     *
     * @see: https://github.com/smalot/pdfparser/issues/586
     */
    public function testImproperFontFallback(): void
    {
        $filename = $this->rootDir.'/samples/ImproperFontFallback.pdf';

        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();

        $this->assertStringContainsString('сделал', $pages[0]->getText());
    }

    /**
     * Tests that a font ID containing a hyphen / dash character was
     * correctly parsed
     *
     * @see: https://github.com/smalot/pdfparser/issues/145
     */
    public function testFontIDWithHyphen(): void
    {
        $pdfObject = $this->getPdfObjectInstance(new Document());

        $fontCommandHyphen = $pdfObject->getCommandsText('/FID-01 15.00 Tf');

        $this->assertEquals('/', $fontCommandHyphen[0]['t']);
        $this->assertEquals('Tf', $fontCommandHyphen[0]['o']);
        $this->assertEquals('FID-01 15.00', $fontCommandHyphen[0]['c']);
    }

    /**
     * Tests that an invalid command does not cause an error, but just
     * returns an empty array
     */
    public function testInvalidCommand(): void
    {
        $pdfObject = $this->getPdfObjectInstance(new Document());

        $validCommand = $pdfObject->getCommandsText('75 rg');

        $this->assertEquals('', $validCommand[0]['t']);
        $this->assertEquals('rg', $validCommand[0]['o']);
        $this->assertEquals('75', $validCommand[0]['c']);

        $invalidCommand = $pdfObject->getCommandsText('75');

        $this->assertEquals([], $invalidCommand);
    }
}
