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
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\XObject\Form;

/**
 * PDFs that emit text as kerned TJ arrays (for fine letter spacing) split a
 * single line into thousands of tiny string operands. formatContent() parks
 * each operand behind a unique placeholder and restores it afterward. Its
 * effort grows linearly with the number of operands.
 *
 * This test builds a content stream with 60,000 such operands and extracts
 * its text, which takes less than a second. The time budget is exceeded by
 * far, if the effort grows quadratically with the number of operands.
 *
 * @see https://github.com/smalot/pdfparser/issues/712
 * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=258 ISO 32000-1:2008, 9.4.3, Table 109 (TJ)
 */
final class KernedTjArrayFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var positive-int
     */
    private const OPERANDS = 60000;

    /**
     * @var string
     */
    protected $content;

    public function init(): void
    {
        // Create a string which represents a PDF that emits text
        // letter-by-letter for fine kerning.
        $operands = '';
        for ($i = 0; $i < self::OPERANDS; ++$i) {
            $operands .= '(a)'.(($i % 20) - 10).' ';
        }

        $this->content = 'BT /F1 12 Tf 10 10 Td ['.$operands.']TJ ET';
    }

    public function run(): void
    {
        $document = new Document();
        $document->init();

        $form = new Form($document, null, $this->content, new Config());
        $header = new Header([
            'Resources' => new Header([
                'XObject' => new Header(['Fr0' => $form]),
            ]),
            'Contents' => new ElementArray([new Element('/Fr0 Do', $document)], $document),
        ]);

        $textArray = (new Page($document, $header))->getTextArray();

        // Each operand provides one character, a space character is appended
        // to the text. The check makes sure the time was spent on extracting
        // the text.
        if (1 !== \count($textArray) || self::OPERANDS + 1 !== \strlen($textArray[0])) {
            throw new \RuntimeException('Text of kerned TJ array was not extracted as expected.');
        }
    }

    public function getMaxEstimatedTime(): int
    {
        return 5;
    }
}
