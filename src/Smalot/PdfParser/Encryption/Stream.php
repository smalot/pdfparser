<?php

/**
 * This file is based on code of tecnickcom/TCPDF PDF library.
 *
 * Original author Nicola Asuni (info@tecnick.com) and
 * contributors (https://github.com/tecnickcom/TCPDF/graphs/contributors).
 *
 * @see https://github.com/tecnickcom/TCPDF
 *
 * Original code was licensed on the terms of the LGPL v3.
 *
 * ------------------------------------------------------------------------------
 *
 * @file This file is part of the PdfParser library.
 *
 * @author  Alastair Irvine <alastair@plug.org.au>
 *
 * @date    2024-01-12
 *
 * @license LGPLv3
 *
 * @url     <https://github.com/smalot/pdfparser>
 *
 *  PdfParser is a pdf library written in PHP, extraction oriented.
 *  Copyright (C) 2017 - Sébastien MALOT <sebastien@malot.fr>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program.
 *  If not, see <http://www.pdfparser.org/sites/default/LICENSE.txt>.
 */

namespace Smalot\PdfParser\Encryption;

/**
 * Handles data decoding, decryption & ciphers, extra parsing, etc.
 */
abstract class Stream
{
    function __construct($key)
    {
        $this->key = $key;
    }


    /**
     * Carve the IV and the cyphertext apart.  Only used by some algorithms.
     *
     * @return array of two strings
     */
    public static function splitBlock(string $block, int $ivLength)
    {
        $iv = \substr($block, 0, $ivLength);
        $cyphertext = \substr($block, $ivLength);
        return [ $iv, $cyphertext ];
    }


    /**
     * Object factory that instantiates the relevant subclass.
     *
     * @param string $algorithm
     * @param        $key file key consisting of byte string of the relevant number of characters
     *
     * @return Stream subclass
     *
     * @throws InvalidAlgorithm if $algorithm is invalid
     */
    public static function make(string $algorithm, string $key)
    {
        switch ($algorithm) {
            case 'RC4':
                return new RC4Stream($key);
                break;

            case 'AES':
                return new AES128Stream($key);
                break;

            case 'AES256':
                return new AES256Stream($key);
                break;

            default:
                throw new InvalidAlgorithm("Unsupported encryption algorithm");
        }
    }
}


class RC4Stream extends Stream
{
    public function decrypt(string $cyphertext, int $num, int $gen)
    {
        // 32 bytes minus 5 bytes of salting
        if (strlen($this->key) <= 27) {
            $key = $this->makeObjectKey($num, $gen);
        } else {
            $key = $this->key;
        }
        //# printf("%d_%d\n", $num, $gen);
        return \openssl_decrypt($cyphertext, "RC4-40", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
    }


    /**
     * Make a tweaked key that is based on info about the object.
     */
    public function makeObjectKey(int $num, int $gen)
    {
        $objectSalt = [
            ($num >> 0) & 0xff,
            ($num >> 8) & 0xff,
            ($num >> 16) & 0xff,
            ($gen >> 0) & 0xff,
            ($gen >> 8) & 0xff
        ];
        $blob = $this->key.\implode(\array_map("chr", $objectSalt));
        $hash = \md5($blob, true);
        return $hash;
    }
}


class AES128Stream extends Stream
{
    public function decrypt(string $block, int $num, int $gen)
    {
        // 32 bytes minus 9 bytes of salting
        if (strlen($key) <= 23) {
            $key = $this->makeObjectKey($num, $gen);
        } else {
            $key = $this->key;
        }

        list($iv, $cyphertext) = self::splitBlock($block, 16);
        return \openssl_decrypt($cyphertext, "aes-128-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
    }


    /**
     * Make a tweaked key that is based on info about the object.
     */
    public function makeObjectKey(int $num, int $gen)
    {
        $objectSalt = [
            ($num >> 0) & 0xff,
            ($num >> 8) & 0xff,
            ($num >> 16) & 0xff,
            ($gen >> 0) & 0xff,
            ($gen >> 8) & 0xff
        ];
        $blob = $this->key.\implode(\array_map("chr", $objectSalt))."sAlT";
        $hash = \md5($blob, true);
        return $hash;
    }
}


class AES256Stream extends Stream
{
    /**
     * 
     */
    public function decrypt(string $block, int $num, int $gen)
    {
        $key = $this->makeObjectKey($num, $gen);
        list($iv, $cyphertext) = self::splitBlock($block, 16);
        return \openssl_decrypt($cyphertext, "aes-256-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
    }


    public function makeObjectKey(int $num, int $gen)
    {
        return $this->key;
    }
}
