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
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;
use Smalot\PdfParser\XObject\Image;

class ParserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new Parser();
    }

    /**
     * Notice: it may fail to run in Scrutinizer because of memory limitations.
     *
     * @group memory-heavy
     */
    public function testParseFile(): void
    {
        $directory = $this->rootDir.'/samples/bugs';

        if (is_dir($directory)) {
            $files = scandir($directory);

            foreach ($files as $file) {
                if (preg_match('/^.*\.pdf$/i', $file)) {
                    try {
                        $document = $this->fixture->parseFile($directory.'/'.$file);
                        $pages = $document->getPages();
                        $this->assertTrue(0 < \count($pages));

                        foreach ($pages as $page) {
                            $content = $page->getText();
                            $this->assertTrue('' !== $content);
                        }
                    } catch (\Exception $e) {
                        if (
                            'Secured pdf file are currently not supported.' !== $e->getMessage()
                            && 0 != strpos($e->getMessage(), 'TCPDF_PARSER')
                        ) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    /**
     * Properly decode international unicode characters
     *
     * @todo the other languages in the test document need work because of issues with UTF-16 decoding (Chinese, Japanese) and missing right-to-left language support
     */
    public function testUnicodeDecoding(): void
    {
        $filename = $this->rootDir.'/samples/InternationalChars.pdf';

        $document = $this->fixture->parseFile($filename);

        $testString_cyrillic = "Лорем ипсум долор сит амет, еу сед либрис долорем инцоррупте. Ут лорем долоре граеце хис, модо \nаппареат сапиентем ут мел. Хис ат лаборе омнесяуе сигниферумяуе, тале анциллае ан еум, ех сед синт \nнобис. Сед модус вивендо цопиосае еа, сапиентем цонцептам хис не, яуандо сплендиде еум те.";
        $testString_greek = "Λορεμ ιπσθμ δολορ σιτ αμετ, τατιον cονστιτθαμ ομιτταντθρ εα σεα, αθδιαμ μανδαμθσ μελ τε. Διcο μθτατ \nινδοcτθμ εοσ ει, ει vιξ σονετ παρτιενδο ινcορρθπτε. Επιcθρι αντιοπαμ εθ νεc, ναμ λεγιμθσ γθβεργρεν ιν. \nVιξ σολετ ρεcτεqθε εα, ηασ νο αλιqθαμ μινιμθμ. Ιδ προ περcιπιτ περιcθλισ δετερρθισσετ, ιν νεc αππετερε \nομιτταντθρ ελοqθεντιαμ, ορατιο δοcτθσ ναμ αδ. Ετ σιτ σολθμ ρεcθσαβο, vιξ θτ λοβορτισ σπλενδιδε \nρεπθδιανδαε.";
        $testString_armenian = "լոռեմ իպսում դոլոռ սիթ ամեթ վիս ին իմպեդիթ ադմոդում ծու ապպառեաթ սծռիպսեռիթ մել մել եթ \nդոմինգ ծոնսեքուունթուռ ծիվիբուս վիվենդում պռոդեսսեթ ադ մեի թիբիքուե ապպառեաթ սիմիլիքուե թե \nվիմ վիխ ծասե սեմպեռ դոլոռեմ եխ եամ եա սթեթ մեդիոծռեմ ծոնսեթեթուռ ռաթիոնիբուս ինթելլեգամ \nմել թե";
        $testString_georgean = "ლორემ იფსუმ დოლორ სით ამეთ ესთ ეთ სონეთ ზრილ მელიუს ელიგენდი თორყუათოს \nელოყუენთიამ ესთ ეხ უსუ ფალლი ალთერა ცეთეროს ინ ეთ ომითთამ თრაცთათოს ჰის ეუ ველ \nალთერუმ ვოლუფთათუმ მაზიმ ფერთინახ ჰენდრერით ინ ფრი ნეც ინ თემფორ ფეთენთიუმ ვერო \nფოსთულანთ ელოყუენთიამ უსუ ნე ან ყუი ლიბერ ეფიცური ასსუევერით იდ ნიბჰ ყუას ჰაბემუს სეა";
        $testString_korean = "그 임기는 4년으로 하며. 이 경우 그 명령에 의하여 개정 또는 폐지되었던 법률은 그 명령이 승인을 얻지 못한 때부터 당연히 효력을 \n회복한다. 가부동수인 때에는 부결된 것으로 본다. 법률과 적법한 절차에 의하지 아니하고는 처벌·보안처분 또는 강제노역을 받지 \n아니한다.";
        $testString_western = 'ÄÖÜöäüßẞ Ññ¡¿ øÅå';

        $this->assertStringContainsString($testString_cyrillic, $document->getText());
        $this->assertStringContainsString($testString_greek, $document->getText());
        $this->assertStringContainsString($testString_armenian, $document->getText());
        $this->assertStringContainsString($testString_georgean, $document->getText());
        $this->assertStringContainsString($testString_korean, $document->getText());
        $this->assertStringContainsString($testString_western, $document->getText());
    }

    /**
     * Tests that xrefs with line breaks between id and position are parsed correctly
     *
     * @see https://github.com/smalot/pdfparser/issues/336
     */
    public function testIssue19(): void
    {
        $fixture = new ParserSub();
        $structure = [
            [
                '<<',
                [
                    [
                        '/',
                        'Type',
                        7735,
                    ],
                    [
                        '/',
                        'ObjStm',
                        7742,
                    ],
                ],
            ],
            [
                'stream',
                '',
                7804,
                [
                    "17\n0",
                    [],
                ],
            ],
        ];
        $document = new Document();

        $fixture->exposedParseObject('19_0', $structure, $document);
        $objects = $fixture->getObjects();

        $this->assertArrayHasKey('17_0', $objects);
    }

    /**
     * Properly decode ANSI encodings without producing scrambled UTF-8 characters
     *
     * @see https://github.com/smalot/pdfparser/issues/202
     * @see https://github.com/smalot/pdfparser/pull/257
     */
    public function testIssue202(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue202.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertEquals('„fööbär“', $document->getText());
    }

    /**
     * Test that issue related pdf can now be parsed
     *
     * @see https://github.com/smalot/pdfparser/issues/267
     */
    public function testIssue267(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue267_array_access_on_int.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertEquals(Image::class, \get_class($document->getObjectById('128_0')));
        $this->assertStringContainsString('4 von 4', $document->getText());
    }

    /**
     * Test that issue related pdf can now be parsed:
     * Too many slashes were being stripped and resulted
     * in malformed encoding of parts of the text content.
     *
     * @see https://github.com/smalot/pdfparser/issues/322
     */
    public function testIssue322(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue322.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString('this text isn’t working properly, I’ve edited it in Google Documents', $document->getText());
    }

    /**
     * Test that issue related pdf can now be parsed:
     * Too many slashes were being stripped and resulted
     * in malformed encoding of parts of the text content.
     *
     * License of the content taken from https://stackoverflow.com in the sample PDF:
     * CC BY-SA 2.5 https://creativecommons.org/licenses/by-sa/2.5/
     *
     * @see https://github.com/smalot/pdfparser/issues/334
     */
    public function testIssue334(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue334.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString('This question already has an answer here', $document->getText());
    }

    /**
     * Test that issue related pdf can now be parsed:
     * Glyphs not in the Postscript lookup table would cause "Notice: Undefined offset"
     *
     * @see https://github.com/smalot/pdfparser/issues/359
     */
    public function testIssue359(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue359.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString(
            'dnia 10 maja 2018 roku o ochronie danych osobowych',
            $document->getText()
        );
        $this->assertStringContainsString('sprawie ochrony osób fizycznych w związku', $document->getText());
        /*
         * @todo Note that the "ł" in przepływu is decoded as a space character. This was already
         * the case before the PR that caused this issue and is not currently covered by this
         * test case. However, this issue should be addressed in the future and its fix can then
         * be incorporated into this test by uncommenting the following assertion.
         */
        // $this->assertStringContainsString('sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy', $document->getText());
    }

    /**
     * Tests if PDF triggers "Call to undefined method Smalot\PdfParser\Header::__toString()".
     *
     * It happened because there was a check missing in Font.php (~ line 109).
     *
     * @see https://github.com/smalot/pdfparser/issues/391
     */
    public function testIssue391(): void
    {
        /**
         * PDF provided by @dhildreth for usage in our test environment.
         *
         * @see https://github.com/smalot/pdfparser/issues/391#issuecomment-783504599
         */
        $filename = $this->rootDir.'/samples/bugs/Issue391.pdf';

        $document = $this->fixture->parseFile($filename);

        // check for an example string (PDF consists of many pages)
        $this->assertStringContainsString(
            '(This Code will be changed while mass production)',
            $document->getText()
        );
    }

    /**
     * Tests if a PDF with null or empty string headers trigger an Exception.
     *
     * It happened because there was a check missing in Parser.php (parseHeaderElement function).
     *
     * @see https://github.com/smalot/pdfparser/issues/557
     */
    public function testIssue557(): void
    {
        /**
         * PDF provided by @DogLoc for usage in our test environment.
         *
         * @see https://github.com/smalot/pdfparser/pull/560#issue-1461437944
         */
        $filename = $this->rootDir.'/samples/bugs/Issue557.pdf';

        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString(
            'Metal Face Inductive Sensor',
            $document->getText()
        );
    }

    /**
     * Tests if an integer overflow triggers a TypeError in Font::uchr.
     *
     * @see https://github.com/smalot/pdfparser/issues/621
     */
    public function testIssue621(): void
    {
        $document = $this->fixture->parseFile($this->rootDir.'/samples/bugs/Issue621.pdf');

        $this->assertStringContainsString('What is a biological product?', $document->getText());
    }

    /**
     * Tests behavior when changing default font space limit (-50).
     *
     * Test is based on testIssue359 (above).
     */
    public function testChangedFontSpaceLimit(): void
    {
        $filename = $this->rootDir.'/samples/bugs/Issue359.pdf';

        $config = new Config();
        $config->setFontSpaceLimit(1); // change default value

        $this->fixture = new Parser([], $config);
        $document = $this->fixture->parseFile($filename);

        $this->assertStringContainsString('dni a 10 maj a 2018', $document->getText());
    }

    /**
     * Tests if a given Config object is really used.
     * Or if a default one is generated, if null was given.
     */
    public function testUsageOfConfigObject(): void
    {
        // check default
        $this->fixture = new Parser([]);
        $this->assertEquals(new Config(), $this->fixture->getConfig());

        // check default 2
        $this->fixture = new Parser([], null);
        $this->assertEquals(new Config(), $this->fixture->getConfig());

        // check given
        $config = new Config();
        $config->setFontSpaceLimit(1000);
        $this->fixture = new Parser([], $config);
        $this->assertEquals($config, $this->fixture->getConfig());
    }

    /**
     * Tests the impact of the retainImageContent config setting on memory usage
     *
     * @group memory-heavy
     *
     * @see https://github.com/smalot/pdfparser/issues/104#issuecomment-883422508
     */
    public function testRetainImageContentImpact(): void
    {
        if (version_compare(\PHP_VERSION, '7.3.0', '<')) {
            $this->markTestSkipped('Garbage collection doesn\'t work reliably enough for this test in PHP < 7.3');
        }

        gc_collect_cycles();
        $baselineMemory = memory_get_usage(true);

        $filename = $this->rootDir.'/samples/bugs/Issue104a.pdf';
        $iterations = 2;

        /*
         * check default (= true)
         */
        $this->fixture = new Parser([]);
        $this->assertTrue($this->fixture->getConfig()->getRetainImageContent());
        $document = null;

        for ($i = 0; $i < $iterations; ++$i) {
            $document = $this->fixture->parseFile($filename);
        }

        $usedMemory = memory_get_usage(true);
        $this->assertGreaterThan($baselineMemory + 180000000, $usedMemory, 'Memory is only '.$usedMemory);
        $this->assertTrue(null != $document && '' !== $document->getText());

        // force garbage collection
        $this->fixture = $document = null;
        gc_collect_cycles();

        /*
         * check false
         */
        $config = new Config();
        $config->setRetainImageContent(false);
        $this->fixture = new Parser([], $config);
        $this->assertEquals($config, $this->fixture->getConfig());

        for ($i = 0; $i < $iterations; ++$i) {
            $document = $this->fixture->parseFile($filename);
        }

        $usedMemory = memory_get_usage(true);
        /*
         * note: the following memory value is set manually and may differ from system to system.
         *       it must be high enough to not produce a false negative though.
         */
        $this->assertLessThan($baselineMemory * 1.05, $usedMemory, 'Memory is '.$usedMemory);
        $this->assertTrue('' !== $document->getText());
    }

    /**
     * Tests handling of encrypted PDF.
     *
     * @see https://github.com/smalot/pdfparser/pull/653
     */
    public function testNoIgnoreEncryption(): void
    {
        $filename = $this->rootDir.'/samples/not_really_encrypted.pdf';
        $threw = false;
        try {
            (new Parser([]))->parseFile($filename);
        } catch (\Exception $e) {
            // we expect an exception to be thrown if an encrypted PDF is encountered.
            $threw = true;
        }
        $this->assertTrue($threw);
    }

    /**
     * Tests behavior if encryption is ignored.
     *
     * @see https://github.com/smalot/pdfparser/pull/653
     */
    public function testIgnoreEncryption(): void
    {
        $config = new Config();
        $config->setIgnoreEncryption(true);

        $filename = $this->rootDir.'/samples/not_really_encrypted.pdf';

        $this->assertTrue((new Parser([], $config))->parseFile($filename) instanceof Document);

        // without the configuration option set, an exception would be thrown.
    }
}

class ParserSub extends Parser
{
    public function exposedParseObject($id, $structure, $document)
    {
        return $this->parseObject($id, $structure, $document);
    }

    public function getObjects(): array
    {
        return $this->objects;
    }
}
