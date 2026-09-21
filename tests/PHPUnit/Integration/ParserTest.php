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
use Smalot\PdfParser\RawData\RawDataParser;
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
     * Provides the raw structure of an object stream.
     *
     * @param string|null $n     value of /N, null leaves the entry out
     * @param string|null $first value of /First, null leaves the entry out
     */
    private function getObjectStreamStructure(string $content, ?string $n, ?string $first): array
    {
        $dictionary = [['/', 'Type', 0], ['/', 'ObjStm', 0]];

        if (null !== $n) {
            array_push($dictionary, ['/', 'N', 0], ['numeric', $n, 0]);
        }

        if (null !== $first) {
            array_push($dictionary, ['/', 'First', 0], ['numeric', $first, 0]);
        }

        return [['<<', $dictionary], ['stream', $content]];
    }

    /**
     * Provides the objects of an object stream: object reference => value of the entry /V.
     *
     * @return array<string, string|null>
     */
    private function parseObjectStream(array $structure): array
    {
        $fixture = new ParserSub();
        $fixture->exposedParseObject('19_0', $structure, new Document());

        $objects = [];
        foreach ($fixture->getObjects() as $id => $object) {
            $objects[$id] = $object->has('V') ? $object->get('V')->getContent() : null;
        }

        return $objects;
    }

    /**
     * /First is the byte offset of the first object, the index in front of it
     * consists of pairs of integers: object number and byte offset of the
     * object relative to the first object.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=53 ISO 32000-1:2008, 7.5.7 (object streams)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=54 ISO 32000-1:2008, 7.5.7, Table 16 (N, First)
     */
    public function testObjectStream(): void
    {
        $structure = $this->getObjectStreamStructure('11 0 12 10 <</V /A>> <</V /B>>', '2', '10');

        $this->assertSame(['11_0' => 'A', '12_0' => 'B'], $this->parseObjectStream($structure));
    }

    /**
     * The first objects of an object stream may be integers. /First tells them
     * apart from the pairs of the index.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=54 ISO 32000-1:2008, 7.5.7, Table 16 (N, First)
     */
    public function testObjectStreamStartingWithIntegerObjects(): void
    {
        $structure = $this->getObjectStreamStructure('11 0 12 3 13 6 42 43 <</V /C>>', '3', '15');

        $this->assertSame(['11_0' => null, '12_0' => null, '13_0' => 'C'], $this->parseObjectStream($structure));

        // /First is sufficient, /N may be missing
        $structure = $this->getObjectStreamStructure('11 0 12 3 13 6 42 43 <</V /C>>', null, '15');

        $this->assertSame(['11_0' => null, '12_0' => null, '13_0' => 'C'], $this->parseObjectStream($structure));
    }

    /**
     * The index ends with the last of its consecutive pairs, unless /First is
     * located between two pairs and the number of pairs in front of it equals
     * /N. The objects are packed without white-space, so each of the cases
     * provides damaged objects, if /First gets used.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     * @see {U}#page=54 ISO 32000-1:2008, 7.5.7, Table 16 (N, First)
     */
    public function testObjectStreamWithImplausibleFirst(): void
    {
        $content = '11 0 12 9 13 18 <</V /A>><</V /B>><</V /C>>';
        $expected = ['11_0' => 'A', '12_0' => 'B', '13_0' => 'C'];

        $cases = [
            '/First fits' => ['3', '16'],
            '/First points to the white-space behind the last pair' => ['3', '15'],
            '/First is located inside of a number' => ['3', '14'],
            '/First is located between two pairs, /N differs from the number of pairs in front of it' => ['3', '10'],
            '/First is located between an object number and its byte offset, /N is missing' => [null, '8'],
            '/First is located inside of the first object' => ['3', '18'],
            '/First is located behind the last object' => ['3', '43'],
        ];

        foreach ($cases as $description => $case) {
            $structure = $this->getObjectStreamStructure($content, $case[0], $case[1]);

            $this->assertSame($expected, $this->parseObjectStream($structure), $description);
        }
    }

    /**
     * Object stream 12815_0 of the file starts with integer objects. Its objects
     * are needed to decode the ligature of "offer" on page 16.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testObjectStreamStartingWithIntegerObjectsInFile(): void
    {
        $document = $this->fixture->parseFile($this->rootDir.'/samples/DocumentWithLotsOfObjects.pdf');

        $this->assertStringContainsString(
            "making an o\u{FB00}er until reach to an end state",
            $document->getPages()[15]->getText()
        );
    }

    /**
     * Every white-space character of PDF separates the integers of the index.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=20 ISO 32000-1:2008, 7.2.2, Table 1 (white-space characters)
     */
    public function testObjectStreamIndexWhitespace(): void
    {
        $index = "11\0 0\t12\f10\r\n";
        $structure = $this->getObjectStreamStructure($index.'<</V /A>> <</V /B>>', '2', (string) \strlen($index));

        $this->assertSame(['11_0' => 'A', '12_0' => 'B'], $this->parseObjectStream($structure));

        // White-space in front of the first pair is part of the index
        $index = "\n 11 0 12 10 ";
        $structure = $this->getObjectStreamStructure($index.'<</V /A>> <</V /B>>', '2', (string) \strlen($index));

        $this->assertSame(['11_0' => 'A', '12_0' => 'B'], $this->parseObjectStream($structure));

        $structure = $this->getObjectStreamStructure($index.'<</V /A>> <</V /B>>', null, null);

        $this->assertSame(['11_0' => 'A', '12_0' => 'B'], $this->parseObjectStream($structure));
    }

    /**
     * Objects are provided in the order of their byte offsets. If two objects
     * share a byte offset, the latter one of the index is provided. An object,
     * whose byte offset is located behind the content, has no entries.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=53 ISO 32000-1:2008, 7.5.7 (object streams)
     */
    public function testObjectStreamWithUnusualIndex(): void
    {
        $cases = [
            'index is sorted by object number instead of byte offset' => [
                '11 10 12 0 <</V /B>> <</V /A>>',
                '2',
                ['12_0' => 'B', '11_0' => 'A'],
            ],
            'two objects share a byte offset' => [
                '11 0 12 0 13 10 <</V /A>> <</V /C>>',
                '3',
                ['12_0' => 'A', '13_0' => 'C'],
            ],
            'byte offset is located behind the content' => [
                '11 0 12 999 13 10 <</V /A>> <</V /C>>',
                '3',
                ['11_0' => 'A', '13_0' => 'C', '12_0' => null],
            ],
        ];

        foreach ($cases as $description => $case) {
            $first = (string) strpos($case[0], '<<');
            $structure = $this->getObjectStreamStructure($case[0], $case[1], $first);

            $this->assertSame($case[2], $this->parseObjectStream($structure), $description);
        }
    }

    /**
     * An index of this size exceeds the limits of a regular expression, which
     * matches all pairs at once.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testLargeObjectStreamIndex(): void
    {
        $objectCount = 20000;
        $index = '';
        $body = '';

        for ($position = 0; $position < $objectCount; ++$position) {
            $index .= (10000 + $position).' '.\strlen($body).' ';
            $body .= '<</V /O'.$position.'>> ';
        }

        $structure = $this->getObjectStreamStructure($index.$body, (string) $objectCount, (string) \strlen($index));
        $objects = $this->parseObjectStream($structure);

        $this->assertCount($objectCount, $objects);
        $this->assertSame('O0', $objects['10000_0']);
        $this->assertSame('O19999', $objects['29999_0']);
    }

    /**
     * Objects are provided as far as possible, if /N or /First are missing or
     * don't fit to the content of the object stream.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testObjectStreamWithUnusableMetadata(): void
    {
        $content = '11 0 12 10 <</V /A>> <</V /B>>';
        $expected = ['11_0' => 'A', '12_0' => 'B'];

        $cases = [
            '/N and /First are missing' => [$content, null, null],
            '/N is too large' => [$content, '3', '10'],
            '/N is too small' => [$content, '1', '10'],
            '/First is behind the content' => [$content, '2', '999'],
            '/First is negative' => [$content, '2', '-5'],
            '/N and /First are real numbers' => [$content, '2.0', '10.0'],
            'integers inside of objects, /N and /First are missing' => ['11 0 12 18 <</V /A /R [1 2]>> <</V /B /R [3 4]>>', null, null],
        ];

        // PHPUnit reports warnings and notices, but the test passes nevertheless.
        // Turn them into exceptions to let the test fail.
        set_error_handler(function (int $severity, string $message) {
            throw new \ErrorException($message, 0, $severity);
        });

        try {
            foreach ($cases as $description => $case) {
                $structure = $this->getObjectStreamStructure($case[0], $case[1], $case[2]);

                $this->assertSame($expected, $this->parseObjectStream($structure), $description);
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Numbers of any order of magnitude neither cause warnings nor exceptions:
     * 32 bit and 64 bit integer limits, the limit of exactly representable
     * floating point numbers and numbers beyond all of them.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testObjectStreamWithNumbersOfAnyOrderOfMagnitude(): void
    {
        $numbers = [
            '2147483647',
            '2147483648',
            '4294967296',
            '9007199254740993',
            '9223372036854775807',
            '9223372036854775808',
            '99999999999999999999',
            str_repeat('9', 400),
        ];

        // PHPUnit reports warnings and notices, but the test passes nevertheless.
        // Turn them into exceptions to let the test fail.
        set_error_handler(function (int $severity, string $message) {
            throw new \ErrorException($message, 0, $severity);
        });

        try {
            foreach ($numbers as $number) {
                // /N and /First: the objects are provided without them
                $content = '11 0 12 10 <</V /A>> <</V /B>>';
                $expected = ['11_0' => 'A', '12_0' => 'B'];

                foreach ([[$number, '11'], ['2', $number], ['2', '-'.$number], [$number, $number]] as $metadata) {
                    $structure = $this->getObjectStreamStructure($content, $metadata[0], $metadata[1]);

                    $this->assertSame($expected, $this->parseObjectStream($structure), '/N '.$metadata[0].', /First '.$metadata[1]);
                }

                // byte offset: it is located behind the content, the other objects are provided
                $index = '11 0 12 '.$number.' 13 10 ';
                $structure = $this->getObjectStreamStructure($index.'<</V /A>> <</V /C>>', '3', (string) \strlen($index));

                $this->assertSame(['11_0' => 'A', '13_0' => 'C', '12_0' => null], $this->parseObjectStream($structure), 'byte offset '.$number);

                // object number: it is taken as it is
                $index = '11 0 '.$number.' 10 ';
                $structure = $this->getObjectStreamStructure($index.'<</V /A>> <</V /B>>', '2', (string) \strlen($index));

                $this->assertSame(['11_0' => 'A', $number.'_0' => 'B'], $this->parseObjectStream($structure), 'object number '.$number);
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * /N and /First may be indirect references, which are unresolved at the
     * time the object stream gets parsed.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testObjectStreamWithIndirectMetadata(): void
    {
        $structure = [
            [
                '<<',
                [
                    ['/', 'Type', 0],
                    ['/', 'ObjStm', 0],
                    ['/', 'N', 0],
                    ['objref', '2_0', 0],
                    ['/', 'First', 0],
                    ['objref', '3_0', 0],
                ],
            ],
            ['stream', '11 0 12 10 <</V /A>> <</V /B>>'],
        ];

        $this->assertSame(['11_0' => 'A', '12_0' => 'B'], $this->parseObjectStream($structure));
    }

    /**
     * Content which doesn't start with an index provides no objects.
     *
     * @see https://github.com/smalot/pdfparser/issues/835
     */
    public function testObjectStreamWithoutIndex(): void
    {
        $this->assertSame([], $this->parseObjectStream($this->getObjectStreamStructure('', '0', '0')));
        $this->assertSame([], $this->parseObjectStream($this->getObjectStreamStructure('<</V /A>>', '1', '0')));
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

    /**
     * Tests a fix for chr() which threw deprecations when running PHP 8.5
     *
     * @see https://github.com/smalot/pdfparser/pull/793
     */
    public function testPullRequest793ChrDeprecationFix(): void
    {
        $document = (new Parser())->parseFile($this->rootDir.'/samples/bugs/PullRequest793.pdf');

        $this->assertEquals('ASCII85 last-tuple overflow test', $document->getText());
    }

    /**
     * Objects used by the tests below.
     *
     * @return array<int, string>
     */
    private function getObjects(): array
    {
        return [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [] /Count 0 >>',
            3 => '<< /Foo (bar) >>',
        ];
    }

    /**
     * Each object is decoded and turned into a PDFObject, before the next one
     * gets decoded.
     */
    public function testParseContentDecodesAndBuildsObjectsAlternately(): void
    {
        $parser = new ParserSub();
        $rawDataParser = $parser->useRawDataParserSpy();

        $document = $parser->parseContent($this->createPdf($this->getObjects()));

        $this->assertSame(
            ['decode 1_0', 'build 1_0', 'decode 2_0', 'build 2_0', 'decode 3_0', 'build 3_0'],
            $rawDataParser->events
        );
        $this->assertSame(['1_0', '2_0', '3_0'], array_keys($document->getObjects()));
    }

    /**
     * An encrypted file is rejected before any of its objects gets decoded.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=51 ISO 32000-1:2008, 7.5.5, Table 15 (entries in the file trailer dictionary)
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=63 ISO 32000-1:2008, 7.6.1 (encryption)
     */
    public function testParseContentEncryptedFileDecodesNoObject(): void
    {
        $parser = new ParserSub();
        $rawDataParser = $parser->useRawDataParserSpy();

        try {
            $parser->parseContent($this->createPdf($this->getObjects(), '/Encrypt 3 0 R '));
            $this->fail('Exception expected');
        } catch (\Exception $e) {
            $this->assertSame('Secured pdf file are currently not supported.', $e->getMessage());
        }

        $this->assertSame([], $rawDataParser->events);
    }

    /**
     * A file, whose cross-reference table only consists of the free entry of object 0.
     *
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=48 ISO 32000-1:2008, 7.5.4 (cross-reference table)
     */
    public function testParseContentWithoutObjects(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Object list not found. Possible secured file.');

        $this->fixture->parseContent($this->createPdf([]));
    }

    /**
     * A Parser instance, which parsed a file with objects before, reports a
     * file without objects as well.
     */
    public function testParseContentWithoutObjectsAfterFileWithObjects(): void
    {
        $document = $this->fixture->parseContent($this->createPdf($this->getObjects()));
        $this->assertCount(3, $document->getObjects());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Object list not found. Possible secured file.');

        $this->fixture->parseContent($this->createPdf([]));
    }

    /**
     * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=47 ISO 32000-1:2008, 7.5.2 (file header)
     */
    public function testParseFileWithDataInFrontOfHeader(): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'pdfparser');
        file_put_contents($filename, "data in front of header\n".$this->createPdf($this->getObjects()));

        try {
            $document = $this->fixture->parseFile($filename);
        } finally {
            unlink($filename);
        }

        $this->assertSame(['1_0', '2_0', '3_0'], array_keys($document->getObjects()));
    }
}

/**
 * Records the order in which objects get decoded by the RawDataParser and
 * built by ParserSub.
 */
class RawDataParserSpy extends RawDataParser
{
    /**
     * @var array<string>
     */
    public $events = [];

    protected function getIndirectObject(string $pdfData, array $xref, string $objRef, int $offset = 0, bool $decoding = true): array
    {
        $this->events[] = 'decode '.$objRef;

        return parent::getIndirectObject($pdfData, $xref, $objRef, $offset, $decoding);
    }
}

class ParserSub extends Parser
{
    public function useRawDataParserSpy(): RawDataParserSpy
    {
        $this->rawDataParser = new RawDataParserSpy();

        return $this->rawDataParser;
    }

    protected function parseObject(string $id, array $structure, ?Document $document)
    {
        if ($this->rawDataParser instanceof RawDataParserSpy) {
            $this->rawDataParser->events[] = 'build '.$id;
        }

        parent::parseObject($id, $structure, $document);
    }

    public function exposedParseObject($id, $structure, $document)
    {
        return $this->parseObject($id, $structure, $document);
    }

    public function getObjects(): array
    {
        return $this->objects;
    }
}
