<?php

/**
 * @file
 *          This file is part of the PdfParser library.
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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Page
 */
class Page extends atoum\test
{
    public function testGetFonts()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isNotEmpty();
        foreach ($fonts as $font) {
            $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
        }
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isNotEmpty();

        // ------------------------------------------------------
        // Document without text.
        $filename = __DIR__.'/../../../../../samples/Document3_pdfcreator_nocompressed.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isEmpty();
        // the second to use cache.
        $fonts = $page->getFonts();
        $this->assert->array($fonts)->isEmpty();
    }

    public function testGetFont()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];

        // the first to load data.
        $font = $page->getFont('R7');
        $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
        $font = $page->getFont('ABC7');
        $this->assert->object($font)->isInstanceOf('\Smalot\PdfParser\Font');
    }

    public function testGetText()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $text = $page->getText();

        $this->assert->string($text)->hasLengthGreaterThan(150);
        $this->assert->string($text)->contains('Document title');
        $this->assert->string($text)->contains('Lorem ipsum');

        $this->assert->string($text)->contains('Calibri');
        $this->assert->string($text)->contains('Arial');
        $this->assert->string($text)->contains('Times');
        $this->assert->string($text)->contains('Courier New');
        $this->assert->string($text)->contains('Verdana');
    }

    public function testExtractRawData()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedRawData = $page->extractRawData();
        $tmItem = $extractedRawData[1];

        $this->assert->array($extractedRawData)->hasSize(172);
        $this->assert->array($tmItem)->hasSize(3);
        $this->assert->array($tmItem)->hasKeys(['t', 'o', 'c']);
        $this->assert->string($tmItem['o'])->contains('Tm');
        $this->assert->string($tmItem['c'])->contains('0.999429 0 0 1 201.96 720.68');
    }

    public function testExtractDecodedRawData()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $extractedDecodedRawData = $page->extractDecodedRawData();
        $tmItem = $extractedDecodedRawData[1];
        $this->assert->array($extractedDecodedRawData)->hasSize(172);
        $this->assert->array($tmItem)->hasSize(3);
        $this->assert->array($tmItem)->hasKeys(['t', 'o', 'c']);
        $this->assert->string($tmItem['o'])->contains('Tm');
        $this->assert->string($tmItem['c'])->contains('0.999429 0 0 1 201.96 720.68');
        $tjItem = $extractedDecodedRawData[2];
        $this->assert->array($tmItem)->hasSize(3);
        $this->assert->array($tmItem)->hasKeys(['t', 'o', 'c']);
        $this->assert->string($tjItem['o'])->contains('TJ');
        $this->assert->string($tjItem['c'][0]['t'])->contains('(');
        $this->assert->string($tjItem['c'][0]['c'])->contains('D');
        $this->assert->string($tjItem['c'][1]['t'])->contains('n');
        $this->assert->string($tjItem['c'][1]['c'])->contains('0.325008');
        $this->assert->string($tjItem['c'][2]['t'])->contains('(');
        $this->assert->string($tjItem['c'][2]['c'])->contains('o');
    }

    public function testGetDataCommands()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataCommands = $page->getDataCommands();
        $tmItem = $dataCommands[0];
        $this->assert->array($dataCommands)->hasSize(166);
        $this->assert->array($tmItem)->hasSize(3);
        $this->assert->array($tmItem)->hasKeys(['t', 'o', 'c']);
        $this->assert->string($tmItem['o'])->contains('Tm');
        $this->assert->string($tmItem['c'])->contains('0.999429 0 0 1 201.96 720.68');
        $tjItem = $dataCommands[1];
        $this->assert->array($tjItem)->hasSize(3);
        $this->assert->array($tjItem)->hasKeys(['t', 'o', 'c']);
        $this->assert->string($tjItem['o'])->contains('TJ');
        $this->assert->string($tjItem['c'][0]['t'])->contains('(');
        $this->assert->string($tjItem['c'][0]['c'])->contains('D');
        $this->assert->string($tjItem['c'][1]['t'])->contains('n');
        $this->assert->string($tjItem['c'][1]['c'])->contains('0.325008');
        $this->assert->string($tjItem['c'][2]['t'])->contains('(');
        $this->assert->string($tjItem['c'][2]['c'])->contains('o');
    }

    public function testGetDataTm()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $item = $dataTm[0];
        $this->assert->array($dataTm)->hasSize(81);
        $this->assert->array($item)->hasSize(2);
        $this->assert->array($item[0])->hasSize(6);
        $this->assert->array($item[0])->containsValues([
                                            '0.999429',
                                            '0',
                                            '0',
                                            '1',
                                            '201.96',
                                            '720.68',
        ]);
        $this->assert->string($item[1])->contains('Document title');
        $item = $dataTm[2];
        $this->assert->array($item[0])->containsValues([
                                            '0.999402',
                                            '0',
                                            '0',
                                            '1',
                                            '70.8',
                                            '673.64',
        ]);
        $this->assert->string($item[1])->contains('Calibri : Lorem ipsum dolor sit amet, consectetur a');
        $item = $dataTm[80];
        $this->assert->array($item[0])->containsValues([
                                            '0.999402',
                                            '0',
                                            '0',
                                            '1',
                                            '343.003',
                                            '81.44',
        ]);
        $this->assert->string($item[1])->contains('nenatis.');

        // ------------------------------------------------------
        // Document is a form
        $filename = __DIR__.'/../../../../../samples/SimpleInvoiceFilledExample1.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $item = $dataTm[2];
        $this->assert->array($dataTm)->hasSize(105);
        $this->assert->array($item)->hasSize(2);
        $this->assert->array($item[0])->hasSize(6);
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '167.3',
                                            '894.58',
        ]);
        $this->assert->string($item[1])->contains('MyName  MyLastName');
        $item = $dataTm[6];
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '681.94',
                                            '877.42',
        ]);
        $this->assert->string($item[1])->contains('1/1/2020');
        $item = $dataTm[8];
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '174.86',
                                            '827.14',
        ]);
        $this->assert->string($item[1])->contains('Purchase 1');

        // ------------------------------------------------------
        // Document is another form of the same type
        $filename = __DIR__.'/../../../../../samples/SimpleInvoiceFilledExample2.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $dataTm = $page->getDataTm();
        $item = $dataTm[2];
        $this->assert->array($dataTm)->hasSize(105);
        $this->assert->array($item)->hasSize(2);
        $this->assert->array($item[0])->hasSize(6);
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '167.3',
                                            '894.58',
        ]);
        $this->assert->string($item[1])->contains("Other'sName  Other'sLastName");
        $item = $dataTm[6];
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '681.94',
                                            '877.42',
        ]);
        $this->assert->string($item[1])->contains('2/2/2020');
        $item = $dataTm[8];
        $this->assert->array($item[0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '174.86',
                                            '827.14',
        ]);
        $this->assert->string($item[1])->contains('Purchase 2');
    }

    public function testGetTextXY()
    {
        // Document with text.
        $filename = __DIR__.'/../../../../../samples/Document1_pdfcreator_nocompressed.pdf';
        $parser = new \Smalot\PdfParser\Parser();
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(201.96, 720.68);
        $this->assert->array($result)->hasSize(1);
        $this->assert->array($result[0])->hasSize(2);
        $this->assert->array($result[0][0])->containsValues([
                                            '0.999429',
                                            '0',
                                            '0',
                                            '1',
                                            '201.96',
                                            '720.68',
        ]);
        $this->assert->string($result[0][1])->contains('Document title');
        $result = $page->getTextXY(201, 720);
        $this->assert->array($result)->hasSize(0);
        $result = $page->getTextXY(201, 720, 1, 1);
        $this->assert->array($result)->hasSize(1);
        $this->assert->array($result[0])->hasSize(2);
        $this->assert->array($result[0][0])->containsValues([
                                            '0.999429',
                                            '0',
                                            '0',
                                            '1',
                                            '201.96',
                                            '720.68',
        ]);
        $this->assert->string($result[0][1])->contains('Document title');

        // ------------------------------------------------------
        // Document is a form
        $filename = __DIR__.'/../../../../../samples/SimpleInvoiceFilledExample1.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(167, 894, 1, 1);
        $this->assert->array($result[0][0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '167.3',
                                            '894.58',
        ]);
        $this->assert->string($result[0][1])->contains('MyName  MyLastName');
        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assert->string($result[0][1])->contains('1/1/2020');
        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assert->string($result[0][1])->contains('Purchase 1');

        // ------------------------------------------------------
        // Document is another form of the same type
        $filename = __DIR__.'/../../../../../samples/SimpleInvoiceFilledExample2.pdf';
        $document = $parser->parseFile($filename);
        $pages = $document->getPages();
        $page = $pages[0];
        $result = $page->getTextXY(167, 894, 1, 1);
        $this->assert->array($result[0][0])->containsValues([
                                            '1',
                                            '0',
                                            '0',
                                            '1',
                                            '167.3',
                                            '894.58',
        ]);
        $this->assert->string($result[0][1])->contains("Other'sName  Other'sLastName");
        $result = $page->getTextXY(681, 877, 1, 1);
        $this->assert->string($result[0][1])->contains('2/2/2020');
        $result = $page->getTextXY(174, 827, 1, 1);
        $this->assert->string($result[0][1])->contains('Purchase 2');
    }
}
