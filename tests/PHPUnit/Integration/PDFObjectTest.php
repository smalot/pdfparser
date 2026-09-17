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

    /**
     * Returns the size of the chunks (in bytes) getSectionsText() processes a
     * formatted stream in. Tests use it to place the chunk boundaries.
     */
    private function getSectionsChunkSize(): int
    {
        return (new \ReflectionClass(PDFObject::class))->getConstant('SECTIONS_CHUNK_SIZE');
    }

    /**
     * Returns a document stream the way getSectionsText() sees it internally:
     * one command per line, lines separated by \r\n.
     */
    private function formatContent(string $content): string
    {
        $formatContent = new \ReflectionMethod('Smalot\PdfParser\PDFObject', 'formatContent');

        // TODO: remove this if-clause when dropping 8.0.x support
        if (version_compare(\PHP_VERSION, '8.1.0', '<')) {
            $formatContent->setAccessible(true);
        }

        return $formatContent->invoke($this->getPdfObjectInstance(new Document()), $content);
    }

    /**
     * Builds a document stream consisting of:
     *
     * 1. a path command whose first operand has $padDigits digits; each
     *    additional digit moves everything behind it by exactly one byte
     * 2. $fillerCount blocks of path commands, which are irrelevant for text
     *    extraction and therefore dropped by getSectionsText()
     * 3. $trailer
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=140 ISO 32000-1:2008, 8.5.2.1, Table 59 (m, c)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=143 ISO 32000-1:2008, 8.5.3.1, Table 60 (S)
     */
    private function buildPaddedStream(int $padDigits, int $fillerCount, string $trailer): string
    {
        return str_repeat('1', $padDigits)." 0 m\n"
            .str_repeat("100.5 200.5 m\n110.5 210.5 120.5 220.5 130.5 230.5 c\nS\n", $fillerCount)
            .$trailer;
    }

    /**
     * Returns a document stream (see buildPaddedStream) which is padded in a
     * way that $measure, applied to the formatted stream, returns $target.
     *
     * @param callable $measure gets the formatted stream, returns a byte position or length
     */
    private function fitPaddedStream(string $trailer, callable $measure, int $target): string
    {
        // Measure two small streams to learn how many bytes one filler block
        // takes up in the formatted stream
        $base = $measure($this->formatContent($this->buildPaddedStream(1, 1, $trailer)));
        $fillerLength = $measure($this->formatContent($this->buildPaddedStream(1, 2, $trailer))) - $base;

        // Get as close as possible using filler blocks, rest is done by digits
        $fillerCount = 1 + intdiv($target - $base, $fillerLength);
        $padDigits = 1 + $target - $base - ($fillerCount - 1) * $fillerLength;

        return $this->buildPaddedStream($padDigits, $fillerCount, $trailer);
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

        // TODO: remove this if-clause when dropping 8.0.x support
        // From documentation > http://php.net/manual/en/reflectionproperty.setaccessible.php:
        // As of PHP 8.1.0, calling this method has no effect; all properties are accessible by default.
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $formatContent->setAccessible(true);
        }

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

        // TODO: remove this if-clause when dropping 8.0.x support
        // From documentation > http://php.net/manual/en/reflectionproperty.setaccessible.php:
        // As of PHP 8.1.0, calling this method has no effect; all properties are accessible by default.
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $formatContent->setAccessible(true);
        }

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

        // TODO: remove this if-clause when dropping 8.0.x support
        // From documentation > http://php.net/manual/en/reflectionproperty.setaccessible.php:
        // As of PHP 8.1.0, calling this method has no effect; all properties are accessible by default.
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $formatContent->setAccessible(true);
        }

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

    /**
     * getSectionsText() processes the formatted stream in line-aligned chunks.
     * The result must never depend on where a chunk boundary falls, so move
     * the boundary over every single byte of a small probe, including the
     * bytes of its \r\n line endings.
     *
     * The probe covers the relevant state transitions: a command kept outside
     * of a text object (q), BT, commands inside the text object, ET, a command
     * which is dropped, because it is located outside of a text object (5 5 m)
     * and another kept one (Q).
     *
     * @see https://github.com/smalot/pdfparser/pull/828
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=256 ISO 32000-1:2008, 9.4.1, Table 107 (BT, ET)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=135 ISO 32000-1:2008, 8.4.4, Table 57 (q, Q)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=140 ISO 32000-1:2008, 8.5.2.1, Table 59 (m)
     */
    public function testGetSectionsTextChunkBoundaryAtEveryPosition(): void
    {
        $chunkSize = $this->getSectionsChunkSize();

        $probe = "q\nBT\n/F1 12 Tf\n(Hello) Tj\nET\n5 5 m\nQ\n";
        $probeFormatted = "q\r\nBT\r\n/F1 12 Tf\r\n(Hello) Tj\r\nET\r\n5 5 m\r\nQ\r\n";
        $trailer = $probe."7 7 m\n8 8 l\nS\nBT\n(Tail) Tj\nET";

        $expected = ['q', 'BT', '/F1 12 Tf', '(Hello) Tj', 'ET', 'Q', 'BT', '(Tail) Tj', 'ET'];

        $positionOfProbe = function (string $formatted) use ($probeFormatted) {
            return strpos($formatted, $probeFormatted);
        };

        // From the \r of the line before the probe up to the second byte of
        // the line following the probe
        $firstOffset = -2;
        $lastOffset = \strlen($probeFormatted) + 1;

        for ($offset = $firstOffset; $offset <= $lastOffset; ++$offset) {
            // $offset is the byte of the probe the chunk boundary falls on
            $content = $this->fitPaddedStream($trailer, $positionOfProbe, $chunkSize - $offset);

            // Make sure the test setup does what it claims to do
            if ($firstOffset === $offset || $lastOffset === $offset) {
                $this->assertSame(
                    $chunkSize - $offset,
                    $positionOfProbe($this->formatContent($content)),
                    'Test setup: probe is not located at the expected position'
                );
            }

            $this->assertSame(
                $expected,
                $this->getPdfObjectInstance(new Document())->getSectionsText($content),
                'Chunk boundary at byte '.$offset.' of the probe'
            );
        }
    }

    /**
     * Formatted streams which are exactly as long as one chunk or exceed it
     * by just a few bytes. In the latter cases the last chunk only consists
     * of the closing ET (or parts of the \r\n in front of it).
     *
     * @see https://github.com/smalot/pdfparser/pull/828
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=256 ISO 32000-1:2008, 9.4.1, Table 107 (BT, ET)
     */
    public function testGetSectionsTextStreamEndsNearChunkBoundary(): void
    {
        $chunkSize = $this->getSectionsChunkSize();

        // formatContent() trims the stream, so it ends with: \r\n(End) Tj\r\nET
        $trailer = "q\nBT\n(End) Tj\nET";

        for ($length = $chunkSize - 1; $length <= $chunkSize + 6; ++$length) {
            $content = $this->fitPaddedStream($trailer, 'strlen', $length);

            // Make sure the test setup does what it claims to do
            $this->assertSame(
                $length,
                \strlen($this->formatContent($content)),
                'Test setup: formatted stream does not have the expected length'
            );

            $this->assertSame(
                ['q', 'BT', '(End) Tj', 'ET'],
                $this->getPdfObjectInstance(new Document())->getSectionsText($content),
                'Formatted stream has a length of '.$length.' bytes'
            );
        }
    }

    /**
     * A stream spanning several chunks in which (almost) every line carries a
     * running number. This way every line which gets lost, duplicated, split
     * or reordered at one of the chunk boundaries is detected, as well as a
     * text object state which isn't carried over to the next chunk.
     *
     * @see https://github.com/smalot/pdfparser/pull/828
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=135 ISO 32000-1:2008, 8.4.4, Table 57 (cm, Q)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=257 ISO 32000-1:2008, 9.4.2, Table 108 (Td)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=140 ISO 32000-1:2008, 8.5.2.1, Table 59 (m)
     */
    public function testGetSectionsTextAcrossMultipleChunks(): void
    {
        $content = '';
        $expected = [];

        // 1. Outside of a text block: only commands relevant for positioning
        //    are kept. These lines span more than one chunk.
        for ($i = 100000; $i < 190000; ++$i) {
            $content .= '1 0 0 1 '.$i." 0 cm\n".$i." 0 m\n";
            $expected[] = '1 0 0 1 '.$i.' 0 cm';
        }

        // 2. Inside of a text block every line has to be kept. The text block
        //    spans more than one chunk.
        $content .= "BT\n";
        $expected[] = 'BT';
        for ($i = 200000; $i < 330000; ++$i) {
            $content .= $i." 0 Td\n";
            $expected[] = $i.' 0 Td';
        }
        $content .= "ET\n";
        $expected[] = 'ET';

        // 3. After the text block: more than one chunk of commands which are
        //    not part of the result
        for ($i = 400000; $i < 530000; ++$i) {
            $content .= $i." 0 m\n";
        }

        $content .= 'Q';
        $expected[] = 'Q';

        // Make sure the test setup does what it claims to do
        $this->assertGreaterThan(
            5 * $this->getSectionsChunkSize(),
            \strlen($this->formatContent($content)),
            'Test setup: formatted stream is too short'
        );

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        // Do not pass the arrays to assertSame() directly, because if they
        // differ, PHPUnit needs a lot of time and memory to generate the diff
        $this->assertCount(\count($expected), $sections);
        $this->assertTrue($expected === $sections, 'Sections differ from expected result');
    }

    /**
     * Chunks are line-aligned, therefore a line which is longer than a chunk
     * has to stay intact. Hexadecimal strings are used to get such lines.
     *
     * @see https://github.com/smalot/pdfparser/pull/828
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=24 ISO 32000-1:2008, 7.3.4.3 (hexadecimal strings)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=258 ISO 32000-1:2008, 9.4.3, Table 109 (Tj)
     */
    public function testGetSectionsTextLineLongerThanChunk(): void
    {
        $lineA = '<'.str_repeat('41', 600000).'> Tj';
        $lineB = '<'.str_repeat('42', 1100000).'> Tj';

        // Make sure the test setup does what it claims to do
        $this->assertGreaterThan($this->getSectionsChunkSize(), \strlen($lineA));
        $this->assertGreaterThan(2 * $this->getSectionsChunkSize(), \strlen($lineB));

        $content = "q\nBT\n".$lineA."\n".$lineB."\n(short) Tj\nET\n5 5 m\nQ";

        $sections = $this->getPdfObjectInstance(new Document())->getSectionsText($content);

        $this->assertCount(7, $sections);
        $this->assertTrue(
            ['q', 'BT', $lineA, $lineB, '(short) Tj', 'ET', 'Q'] === $sections,
            'Sections differ from expected result'
        );
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
