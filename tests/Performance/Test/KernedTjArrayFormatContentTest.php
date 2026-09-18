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
 * each operand behind a unique placeholder and restores it afterward.
 * Restoring them with one str_replace() per placeholder scans the whole
 * content stream once per operand, i.e. O(operands * length) - quadratic in
 * the number of operands.
 *
 * This test builds a content stream with 20,000 such operands and extracts
 * its text. With the single-pass strtr() restoration this runs in ~1.5s here;
 * with the previous per-placeholder str_replace() loop it took ~10s. The time
 * budget below fails if the quadratic behaviour is reintroduced.
 *
 * @see https://github.com/smalot/pdfparser/issues/712
 */
class KernedTjArrayFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var string
     */
    protected $content;

    public function init(): void
    {
        // Like a PDF that emits text letter-by-letter for fine kerning.
        $operands = '';
        for ($i = 0; $i < 20000; ++$i) {
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

        (new Page($document, $header))->getTextArray();
    }

    public function getMaxEstimatedTime(): int
    {
        return 5;
    }
}
