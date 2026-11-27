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
 * Tagged PDFs wrap each piece of text in a marked-content sequence, whose
 * property list is a dictionary in front of the BDC operator.
 * formatContent() parks each dictionary behind a placeholder and restores it
 * afterward. Its effort must grow linearly with the number of dictionaries.
 *
 * This test extracts the text of 24,000 marked-content sequences (about
 * 700 KB of content stream). The time budget is exceeded by far, if the
 * effort grows quadratically with the number of dictionaries.
 *
 * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=561 ISO 32000-1:2008, 14.6.1, Table 320 (BDC, EMC)
 */
final class MarkedContentDictionariesFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var positive-int
     */
    private const SEQUENCES = 24000;

    /**
     * @var string
     */
    protected $content;

    public function init(): void
    {
        $this->content = 'BT /F1 12 Tf 10 10 Td '
            .str_repeat('/P <</MCID 0>> BDC (a) Tj EMC ', self::SEQUENCES)
            .'ET';
    }

    public function run(): void
    {
        $textArray = $this->getTextArrayOfFormContent($this->content);

        // Each sequence provides one character, a space character is
        // appended to the text. The check makes sure the time was spent on
        // extracting the text.
        if ([str_repeat('a', self::SEQUENCES).' '] !== $textArray) {
            throw new \RuntimeException('Text of marked-content sequences was not extracted as expected.');
        }
    }

    public function getMaxEstimatedTime(): int
    {
        return 5;
    }
}
