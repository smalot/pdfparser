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
 * formatContent() parks each inline image (BI ... ID ... EI) behind a
 * placeholder and restores it afterward. Its effort must grow linearly with
 * the number of inline images.
 *
 * This test extracts the text behind 24,000 inline images (about 600 KB of
 * content stream). The time budget is exceeded by far, if the effort grows
 * quadratically with the number of inline images.
 *
 * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=222 ISO 32000-1:2008, 8.9.7 (inline images)
 */
final class InlineImagesFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var positive-int
     */
    private const IMAGES = 24000;

    /**
     * @var string
     */
    protected $content;

    public function init(): void
    {
        $this->content = str_repeat('q BI /W 1 /H 1 ID x EI Q ', self::IMAGES)
            .'BT /F1 12 Tf 10 10 Td (a) Tj ET';
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
        return 5;
    }
}
