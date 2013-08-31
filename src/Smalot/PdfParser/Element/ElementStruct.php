<?php

namespace Smalot\PdfParser\Element;

use Smalot\PdfParser\Element;
use Smalot\PdfParser\Document;
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

            return Object::parse($document, trim($sub) . "\n");
        }

        return false;
    }
}
