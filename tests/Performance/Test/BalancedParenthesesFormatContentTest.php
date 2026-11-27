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
 * A literal string may contain unescaped parentheses, as long as they are
 * balanced. formatContent() has to find the closing parenthesis of each
 * string operand. Its effort must grow linearly with the length of the
 * string, no matter how many parentheses it contains.
 *
 * This test extracts the text of two string operands of 20,000 parentheses
 * each (about 40 KB of content stream):
 *
 * - nested: "(((...)))"
 * - flat:   "(()()...())"
 *
 * The time budget is exceeded by far, if the effort grows quadratically with
 * the number of parentheses.
 *
 * @see https://opensource.adobe.com/dc-acrobat-sdk-docs/pdfstandards/PDF32000_2008.pdf#page=23 ISO 32000-1:2008, 7.3.4.2 (literal strings)
 */
final class BalancedParenthesesFormatContentTest extends AbstractPerformanceTest
{
    /**
     * @var positive-int
     */
    private const PAIRS = 10000;

    /**
     * @var array<string, string> content of the string operand, keyed by shape
     */
    protected $strings = [];

    public function init(): void
    {
        $this->strings = [
            'nested' => str_repeat('(', self::PAIRS).str_repeat(')', self::PAIRS),
            'flat' => str_repeat('()', self::PAIRS),
        ];
    }

    public function run(): void
    {
        foreach ($this->strings as $shape => $string) {
            $textArray = $this->getTextArrayOfFormContent('BT /F1 12 Tf 10 10 Td ('.$string.') Tj ET');

            // The parentheses are part of the text, a space character is
            // appended to it. The check makes sure the time was spent on
            // extracting the text.
            if ([$string.' '] !== $textArray) {
                throw new \RuntimeException('Text of string operand with '.$shape.' parentheses was not extracted as expected.');
            }
        }
    }

    public function getMaxEstimatedTime(): int
    {
        return 5;
    }
}
