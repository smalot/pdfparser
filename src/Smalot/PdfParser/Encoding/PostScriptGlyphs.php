<?php

// Source : https://github.com/OpenPrinting/cups-filters/blob/master/fontembed/aglfn13.c

namespace Smalot\PdfParser\Encoding;

class PostScriptGlyphs
{
    private static $glyphs = null;

    public static function getGlyphs()
    {
        if (null === self::$glyphs) {
            self::$glyphs = json_decode(file_get_contents(__DIR__.'/PostScriptGlyphs.json'), true);
        }

        return self::$glyphs;
    }

    public static function getCodePoint($glyph)
    {
        return hexdec(static::getGlyphs()[$glyph]);
    }
}
