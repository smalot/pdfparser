<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\PdfParser;

/**
 * Class Parser
 *
 * @package Smalot\PdfParser
 */
class Parser
{
    /**
     * Parse PDF file
     *
     * @param string $filename
     *
     * @return string
     */
    public static function parseFile($filename)
    {
        $pdf = Document::parseFile($filename);

        return self::extractText($pdf);
    }

    /**
     * Parse PDF content
     *
     * @param string $content
     *
     * @return string
     */
    public static function parseContent($content)
    {
        $pdf = Document::parseContent($content);

        return self::extractText($pdf);
    }

    /**
     * Convert a PDF into text.
     *
     * @return string The extracted text from the PDF
     */
    protected static function extractText(Document $pdf)
    {
        $pages = $pdf->getPages();
        $texts = array();

//        var_dump($pdf->getObjectById(1227)->getContent());

        return $pages[1]->getText();

        foreach ($pages as $page) {
            // Add a new text block if not empty.
            if ($text = $page->getText()) {
                $texts[] = $text;
            }
        }

        return implode("\n\n", $texts);
    }
}
