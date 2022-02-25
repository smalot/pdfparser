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

use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Encoding;
use Smalot\PdfParser\Font;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\PDFObject;
use Tests\Smalot\PdfParser\TestCase;

class FontTest extends TestCase
{
    public function testGetName(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        $font = reset($fonts);

        $this->assertEquals('OJHCYD+Cambria,Bold', $font->getName());
    }

    public function testGetType(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        $font = reset($fonts);

        $this->assertEquals('TrueType', $font->getType());
    }

    public function testGetDetails(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        $font = reset($fonts);
        $reference = [
            'Name' => 'OJHCYD+Cambria,Bold',
            'Type' => 'TrueType',
            'Encoding' => 'Ansi',
            'BaseFont' => 'OJHCYD+Cambria,Bold',
            'FontDescriptor' => [
                'Type' => 'FontDescriptor',
                'FontName' => 'OJHCYD+Cambria,Bold',
                'Flags' => 4,
                'Ascent' => 699,
                'CapHeight' => 699,
                'Descent' => -7,
                'ItalicAngle' => 0,
                'StemV' => 128,
                'MissingWidth' => 658,
            ],
            'ToUnicode' => [
                'Filter' => 'FlateDecode',
                'Length' => 219,
            ],
            'FirstChar' => 1,
            'LastChar' => 11,
            'Widths' => [
                0 => 705,
                1 => 569,
                2 => 469,
                3 => 597,
                4 => 890,
                5 => 531,
                6 => 604,
                7 => 365,
                8 => 220,
                9 => 314,
                10 => 308,
            ],
            'Subtype' => 'TrueType',
        ];
        $this->assertEquals($reference, $font->getDetails());
    }

    public function testTranslateChar(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        $font = reset($fonts);

        $this->assertEquals('D', $font->translateChar("\x01"));
        $this->assertEquals('o', $font->translateChar("\x02"));
        $this->assertEquals('c', $font->translateChar("\x03"));
        $this->assertEquals('u', $font->translateChar("\x04"));
        $this->assertEquals(Font::MISSING, $font->translateChar("\x99"));
    }

    /**
     * Tests buggy behavior of #364.
     *
     * In some cases Front::translateChar calls Encoding::__toString, which doesn't exist.
     *
     * Resulting error: Call to undefined method Smalot\PdfParser\Encoding::__toString()
     *
     * @see https://github.com/smalot/pdfparser/issues/364
     */
    public function testTranslateCharIssue364(): void
    {
        /*
         * Approach: we provoke the __toString call with a minimal set of input data.
         */
        $doc = new Document();

        $header = new Header(['BaseEncoding' => new Element('StandardEncoding')]);

        $encoding = new Encoding($doc, $header);
        $encoding->init();

        $font = new Font($doc, new Header(['Encoding' => $encoding]));
        $font->init();

        // without the fix from #378, calling translateChar would raise "undefined method" error
        $this->assertEquals('?', $font->translateChar('t'));
    }

    public function testLoadTranslateTable(): void
    {
        $document = new Document();

        $content = '<</Type/Font /Subtype /Type0 /ToUnicode 2 0 R>>';
        $header = Header::parse($content, $document);
        $font = new Font($document, $header);

        $content = '/CIDInit /ProcSet findresource begin
14 dict begin
begincmap
/CIDSystemInfo
<< /Registry (Adobe)
/Ordering (UCS)
/Supplement 0
>> def
/CMapName /Adobe-Identity-UCS def
/CMapType 2 def
1 begincodespacerange
<0000> <FFFF>
endcodespacerange
3 beginbfchar
<0003> <0020>
<000F> <002C>
<0011> <002E>
endbfchar
2 beginbfrange
<0013> <0016> <0030>
<0018> <001C> <0035>
endbfrange
7 beginbfchar
<0023> <0040>
<0026> <0043>
<0028> <0045>
<0030> <004D>
<0033> <0050>
<0035> <0052>
<0039> <0056>
endbfchar
4 beginbfrange
<0044> <004C> <0061>
<004F> <0052> <006C>
<0054> <0059> <0071>
<005B> <005C> <0078>
endbfrange
4 beginbfchar
<0070> <00E9>
<00AB> <2026>
<00B0> <0153>
<00B6> <2019>
endbfchar
1 beginbfrange
<0084> <0086> [<0061> <0071> <0081>]
endbfrange
endcmap
CMapName currentdict /CMap defineresource pop
end
end';
        $unicode = new PDFObject($document, null, $content);

        $document->setObjects(['1_0' => $font, '2_0' => $unicode]);

        $font->init();
        // Test reload
        $table = $font->loadTranslateTable();

        $this->assertEquals(47, \count($table));

        // Test chars
        $this->assertEquals(' ', $table[3]);
        $this->assertEquals(',', $table[15]);
        $this->assertEquals('.', $table[17]);
        $this->assertEquals('@', $table[35]);
        $this->assertEquals('V', $table[57]);

        // Test ranges
        $this->assertEquals('r', $table[85]);
        $this->assertEquals('y', $table[92]);
    }

    public function testDecodeHexadecimal(): void
    {
        $hexa = '<322041>';
        $this->assertEquals('2 A', Font::decodeHexadecimal($hexa));
        $this->assertEquals('2 A', Font::decodeHexadecimal($hexa, false));
        $this->assertEquals('(2 A)', Font::decodeHexadecimal($hexa, true));

        $hexa = '<003200200041>';
        $this->assertEquals("\x002\x00 \x00A", Font::decodeHexadecimal($hexa));
        $this->assertEquals("\x002\x00 \x00A", Font::decodeHexadecimal($hexa, false));
        $this->assertEquals("(\x002\x00 \x00A)", Font::decodeHexadecimal($hexa, true));

        $hexa = '<00320020> 8 <0041>';
        $this->assertEquals("\x002\x00  8 \x00A", Font::decodeHexadecimal($hexa));
        $this->assertEquals("\x002\x00  8 \x00A", Font::decodeHexadecimal($hexa, false));
        $this->assertEquals("(\x002\x00 ) 8 (\x00A)", Font::decodeHexadecimal($hexa, true));

        $hexa = '<3220> 8 <41>';
        $this->assertEquals('2  8 A', Font::decodeHexadecimal($hexa));
        $this->assertEquals('2  8 A', Font::decodeHexadecimal($hexa, false));
        $this->assertEquals('(2 ) 8 (A)', Font::decodeHexadecimal($hexa, true));

        $hexa = '<00320020005C>-10<0041>';
        $this->assertEquals("\x002\x00 \x00\\-10\x00A", Font::decodeHexadecimal($hexa));
        $this->assertEquals("\x002\x00 \x00\\-10\x00A", Font::decodeHexadecimal($hexa, false));
        $this->assertEquals("(\x002\x00 \x00\\\\)-10(\x00A)", Font::decodeHexadecimal($hexa, true));

        // If it contents XML, the function need to return the same value.
        $hexa = '<?xml version="1.0"?><body xmlns="http://www.w3.org/1999/xhtml" xmlns:xfa="http://www.xfa.org/schema/xfa-data/1.0/" xfa:APIVersion="Acrobat:19.10.0" xfa:spec="2.0.2"  style="font-size:12.0pt;text-align:left;color:0000;font-weight:normal;font-style:norm\
al;font-family:Helvetica,sans-serif;font-stretch:normal"><p><span style="font-family:Helvetica">Example</span></p></body>';
        $this->assertEquals($hexa, Font::decodeHexadecimal($hexa));

        // hexadecimal string with a line break should not return the input string
        // addressing issue #273: https://github.com/smalot/pdfparser/issues/273
        $hexa = "<0027004c0056005300520051004c0045004c004f004c005d0044006f006d0052001d000300560048005b00570044001000490048004c00550044000f0003001400170003004700480003004900480059004800550048004c00550052000300470048000300\n15001300150013>";
        $this->assertEquals("\x0\x27\x0\x4c\x0\x56\x0\x53\x0\x52\x0\x51\x0\x4c\x0\x45\x0\x4c\x0\x4f\x0\x4c\x0\x5d\x0\x44\x0\x6f\x0\x6d\x0\x52\x0\x1d\x0\x3\x0\x56\x0\x48\x0\x5b\x0\x57\x0\x44\x0\x10\x0\x49\x0\x48\x0\x4c\x0\x55\x0\x44\x0\xf\x0\x3\x0\x14\x0\x17\x0\x3\x0\x47\x0\x48\x0\x3\x0\x49\x0\x48\x0\x59\x0\x48\x0\x55\x0\x48\x0\x4c\x0\x55\x0\x52\x0\x3\x0\x47\x0\x48\x0\x3\x0\x15\x0\x13\x0\x15\x0\x13", Font::decodeHexadecimal($hexa));
    }

    public function testDecodeOctal(): void
    {
        $this->assertEquals('AB C', Font::decodeOctal('\\101\\102\\040\\103'));
        $this->assertEquals('AB CD', Font::decodeOctal('\\101\\102\\040\\103D'));
        $this->assertEquals('AB \199', Font::decodeOctal('\\101\\102\\040\\199'));
    }

    public function testDecodeEntities(): void
    {
        $this->assertEquals('File Type', Font::decodeEntities('File#20Type'));
        $this->assertEquals('File# Ty#pe', Font::decodeEntities('File##20Ty#pe'));
    }

    public function testDecodeUnicode(): void
    {
        $this->assertEquals('AB', Font::decodeUnicode("\xFE\xFF\x00A\x00B"));
    }

    public function testDecodeText(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        // Cambria font
        $font = reset($fonts);
        $commands = [
            [
                't' => '',
                'c' => "\x01\x02",
            ],
            [
                't' => 'n',
                'c' => -10,
            ],
            [
                't' => '',
                'c' => "\x03",
            ],
            [
                't' => '',
                'c' => "\x04",
            ],
            [
                't' => 'n',
                'c' => -100,
            ],
            [
                't' => '<',
                'c' => '01020304',
            ],
        ];
        $this->assertEquals('Docu Docu', $font->decodeText($commands));

        //Check if ANSI/Unicode detection is working properly
        $filename = $this->rootDir.'/samples/bugs/Issue95_ANSI.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();
        $font = reset($fonts);
        $commands = [
            [
                't' => '<',
                'c' => 'E6F6FC', //ANSI encoded string
            ],
        ];
        $this->assertEquals('æöü', $font->decodeText($commands));
    }

    /**
     * Font could have indirect encoding without `/Type /Encoding`
     * which would be instance of PDFObject class (but not Encoding or ElementString).
     *
     * @see https://github.com/smalot/pdfparser/pull/500
     */
    public function testDecodeTextForFontWithIndirectEncodingWithoutTypeEncoding(): void
    {
        $filename = $this->rootDir.'/samples/bugs/PullRequest500.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page1 = reset($pages);
        $page1Text = $page1->getText();
        $expectedText = <<<TEXT
Export\u{a0}transakční\u{a0}historie
Typ\u{a0}produktu:\u{a0}Podnikatelský\u{a0}účet\u{a0}Maxi
Číslo\u{a0}účtu:\u{a0}0000000000/0000
Počáteční\u{a0}zůstatek: 000\u{a0}000,00\u{a0}Kč
Konečný\u{a0}zůstatek: 000\u{a0}000,00\u{a0}Kč
Cena\u{a0}za\u{a0}služby
TEXT;

        $this->assertEquals($expectedText, trim($page1Text));
    }

    /**
     * Tests buggy behavior which lead to:
     *
     *      Call to a member function getFontSpaceLimit() on null
     *
     * @see https://github.com/smalot/pdfparser/pull/403
     *
     * @doesNotPerformAssertions
     */
    public function testTriggerGetFontSpaceLimitOnNull(): void
    {
        // error is triggered, if we set the fourth parameter to null
        $font = new Font(new Document(), null, null, new Config());

        // both functions can trigger the error
        $font->decodeText([]);
        $font->getTextArray();
    }

    public function testXmlContent(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue18.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $text = trim($pages[0]->getText());

        $this->assertEquals('Example PDF', $text);
    }

    /**
     * Create an instance of Header containing an instance of Encoding that doesn't have a BaseEncoding.
     * Test if the Font won't raise a exception because Encoding don't have BaseEncoding.
     */
    public function testEncodingWithoutBaseEncoding(): void
    {
        $document = new Document();
        $header = new Header(['Encoding' => new Encoding($document)]);
        $font = new Font(new Document(), $header);
        $font->setTable([]);
        $this->assertEquals('?', $font->translateChar('a'));
    }

    public function testCalculateTextWidth(): void
    {
        $filename = $this->rootDir.'/samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = $this->getParserInstance();
        $document = $parser->parseFile($filename);
        $fonts = $document->getFonts();

        $font = $fonts['7_0'];
        $widths = $font->getDetails()['Widths'];
        $this->assertEquals($widths[0], $font->calculateTextWidth('D'));
        $this->assertEquals($widths[10], $font->calculateTextWidth('l'));

        $width = $font->calculateTextWidth('Calibri', $missing);
        $this->assertEquals(936, $width);
        $this->assertEquals(['C', 'a', 'b', 'r'], $missing);

        $width = $fonts['9_0']->calculateTextWidth('Calibri', $missing);
        $this->assertEquals(2573, $width);
        $this->assertEquals([], $missing);
    }
}
