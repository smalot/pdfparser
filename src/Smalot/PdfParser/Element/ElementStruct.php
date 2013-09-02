<?php

/**
 * @file
 * This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-2.0
 * @url     <https://github.com/smalot/pdfparser>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Header;
use Smalot\PdfParser\Object;

/**
 * Class ElementStruct
 * @package Smalot\PdfParser\Element
 */
class ElementStruct extends Element
{
    /**
     * @param string   $content
     * @param Document $document
     * @param int      $offset
     *
     * @return bool|ElementStruct
     */
    public static function parse($content, Document $document = null, &$offset = 0)
    {
        if (preg_match('/^\s*<<(?<struct>.*)/is', $content)) {
            preg_match_all('/(.*?)(<<|>>)/s', trim($content), $matches);

            $level = 0;
            $sub   = '';
            foreach ($matches[0] as $part) {
                $sub .= $part;
                $level += (strpos($part, '<<') !== false ? 1 : -1);
                if ($level <= 0) {
                    break;
                }
            }

            $offset = strpos($content, '<<') + strlen(rtrim($sub));

            // Removes '<<' and '>>'.
            $sub = trim(preg_replace('/^\s*<<(.*)>>\s*$/s', '\\1', $sub));

            $position = 0;
            $elements = Element::parse($sub, $document, $position);
            $header   = new Header($elements, $document);

            return $header;
        }

        return false;
    }
}
