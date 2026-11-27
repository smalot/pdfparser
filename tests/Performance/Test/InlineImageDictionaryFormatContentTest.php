<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 */

namespace PerformanceTests\Test;

use PerformanceTests\AbstractPerformanceTest;

/**
 * Before formatContent() treats "BI ... ID ... EI" as an inline image, it
 * makes sure the dictionary between BI and ID is not part of a string. To do
 * so, it removes the balanced pairs of parentheses from the dictionary. Its
 * effort must grow linearly with the length of the dictionary, no matter how
 * deeply the parentheses are nested.
 *
 * This test extracts the text behind 200 inline images, whose dictionaries
 * contain a string of 6,000 nested parentheses each (about 1.2 MB of content
 * stream). Without nesting, the text is extracted in a few milliseconds. The
 * time budget is exceeded by far, if the effort grows quadratically with the
 * nesting depth.
 *
 * Each image stays below 64 KB, larger ones are covered by PHPUnit tests.
 *
 * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=222 ISO 32000-1:2008, 8.9.7 (inline images)
 */
final class InlineImageDictionaryFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var positive-int
     */
    private const IMAGES = 200;

    /**
     * @var positive-int
     */
    private const PAIRS = 3000;

    /**
     * @var string
     */
    protected $content;

    public function init(): void
    {
        $image = 'q BI /W 1 /H 1 /X ('
            .str_repeat('(', self::PAIRS).str_repeat(')', self::PAIRS)
            .') ID x EI Q ';

        $this->content = str_repeat($image, self::IMAGES).'BT /F1 12 Tf 10 10 Td (a) Tj ET';
    }

    public function run(): void
    {
        // The check makes sure the time was spent on extracting the text
        if (['a '] !== $this->getTextArrayOfFormContent($this->content)) {
            throw new \RuntimeException('Text behind inline images was not extracted as expected.');
        }
    }

    public function getMaxEstimatedTime(): int
    {
        return 1;
    }
}
