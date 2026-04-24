<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 *
 * @date    2026-04-24
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 */

namespace Smalot\PdfParser\RawData;

final class MemoryLimit
{
    /**
     * Converts PHP ini memory values (for example "128M", "1G", "-1") to bytes.
     */
    public static function toBytes(string $value): int
    {
        $value = trim($value);
        if ('' === $value || '-1' === $value) {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;
        switch ($unit) {
            case 'g':
                return $number * 1024 * 1024 * 1024;

            case 'm':
                return $number * 1024 * 1024;

            case 'k':
                return $number * 1024;

            default:
                return (int) $value;
        }
    }
}