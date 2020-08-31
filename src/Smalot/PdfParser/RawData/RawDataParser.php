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
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-01-06
 *
 * @license LGPLv3
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

namespace Smalot\PdfParser\RawData;

use Exception;

class RawDataParser
{
    /**
     * Configuration array.
     */
    protected $cfg = [
        // if `true` ignore filter decoding errors
        'ignore_filter_decoding_errors' => true,
        // if `true` ignore missing filter decoding errors
        'ignore_missing_filter_decoders' => true,
    ];

    protected $filterHelper;
    protected $objects;

    /**
     * @param array $cfg Configuration array, default is []
     */
    public function __construct($cfg = [])
    {
        // merge given array with default values
        $this->cfg = array_merge($this->cfg, $cfg);

        $this->filterHelper = new FilterHelper();
    }

    /**
     * Decode the specified stream.
     *
     * @param string $pdfData PDF data
     * @param array  $xref
     * @param array  $sdic    Stream's dictionary array
     * @param string $stream  Stream to decode
     *
     * @return array containing decoded stream data and remaining filters
     */
    protected function decodeStream($pdfData, $xref, $sdic, $stream)
    {
        // get stream length and filters
        $slength = \strlen($stream);
        if ($slength <= 0) {
            return ['', []];
        }
        $filters = [];
        foreach ($sdic as $k => $v) {
            if ('/' == $v[0]) {
                if (('Length' == $v[1]) and (isset($sdic[($k + 1)])) and ('numeric' == $sdic[($k + 1)][0])) {
                    // get declared stream length
                    $declength = (int) ($sdic[($k + 1)][1]);
                    if ($declength < $slength) {
                        $stream = substr($stream, 0, $declength);
                        $slength = $declength;
                    }
                } elseif (('Filter' == $v[1]) and (isset($sdic[($k + 1)]))) {
                    // resolve indirect object
                    $objval = $this->getObjectVal($pdfData, $xref, $sdic[($k + 1)]);
                    if ('/' == $objval[0]) {
                        // single filter
                        $filters[] = $objval[1];
                    } elseif ('[' == $objval[0]) {
                        // array of filters
                        foreach ($objval[1] as $flt) {
                            if ('/' == $flt[0]) {
                                $filters[] = $flt[1];
                            }
                        }
                    }
                }
            }
        }

        // decode the stream
        $remaining_filters = [];
        foreach ($filters as $filter) {
            if (\in_array($filter, $this->filterHelper->getAvailableFilters())) {
                try {
                    $stream = $this->filterHelper->decodeFilter($filter, $stream);
                } catch (Exception $e) {
                    $emsg = $e->getMessage();
                    if ((('~' == $emsg[0]) && !$this->cfg['ignore_missing_filter_decoders'])
                        || (('~' != $emsg[0]) && !$this->cfg['ignore_filter_decoding_errors'])
                    ) {
                        throw new Exception($e->getMessage());
                    }
                }
            } else {
                // add missing filter to array
                $remaining_filters[] = $filter;
            }
        }

        return [$stream, $remaining_filters];
    }

    /**
     * Decode the Cross-Reference section
     *
     * @param string $pdfData   PDF data
     * @param int    $startxref Offset at which the xref section starts (position of the 'xref' keyword)
     * @param array  $xref      Previous xref array (if any)
     *
     * @return array containing xref and trailer data
     */
    protected function decodeXref($pdfData, $startxref, $xref = [])
    {
        $startxref += 4; // 4 is the length of the word 'xref'
        // skip initial white space chars: \x00 null (NUL), \x09 horizontal tab (HT), \x0A line feed (LF), \x0C form feed (FF), \x0D carriage return (CR), \x20 space (SP)
        $offset = $startxref + strspn($pdfData, "\x00\x09\x0a\x0c\x0d\x20", $startxref);
        // initialize object number
        $obj_num = 0;
        // search for cross-reference entries or subsection
        while (preg_match('/([0-9]+)[\x20]([0-9]+)[\x20]?([nf]?)(\r\n|[\x20]?[\r\n])/', $pdfData, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            if ($matches[0][1] != $offset) {
                // we are on another section
                break;
            }
            $offset += \strlen($matches[0][0]);
            if ('n' == $matches[3][0]) {
                // create unique object index: [object number]_[generation number]
                $index = $obj_num.'_'.(int) ($matches[2][0]);
                // check if object already exist
                if (!isset($xref['xref'][$index])) {
                    // store object offset position
                    $xref['xref'][$index] = (int) ($matches[1][0]);
                }
                ++$obj_num;
            } elseif ('f' == $matches[3][0]) {
                ++$obj_num;
            } else {
                // object number (index)
                $obj_num = (int) ($matches[1][0]);
            }
        }
        // get trailer data
        if (preg_match('/trailer[\s]*<<(.*)>>/isU', $pdfData, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
            $trailer_data = $matches[1][0];
            if (!isset($xref['trailer']) or empty($xref['trailer'])) {
                // get only the last updated version
                $xref['trailer'] = [];
                // parse trailer_data
                if (preg_match('/Size[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['size'] = (int) ($matches[1]);
                }
                if (preg_match('/Root[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['root'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
                }
                if (preg_match('/Encrypt[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['encrypt'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
                }
                if (preg_match('/Info[\s]+([0-9]+)[\s]+([0-9]+)[\s]+R/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['info'] = (int) ($matches[1]).'_'.(int) ($matches[2]);
                }
                if (preg_match('/ID[\s]*[\[][\s]*[<]([^>]*)[>][\s]*[<]([^>]*)[>]/i', $trailer_data, $matches) > 0) {
                    $xref['trailer']['id'] = [];
                    $xref['trailer']['id'][0] = $matches[1];
                    $xref['trailer']['id'][1] = $matches[2];
                }
            }
            if (preg_match('/Prev[\s]+([0-9]+)/i', $trailer_data, $matches) > 0) {
                // get previous xref
                $xref = $this->getXrefData($pdfData, (int) ($matches[1]), $xref);
            }
        } else {
            throw new Exception('Unable to find trailer');
        }

        return $xref;
    }

    /**
     * Decode the Cross-Reference Stream section
     *
     * @param string $pdfData   PDF data
     * @param int    $startxref Offset at which the xref section starts
     * @param array  $xref      Previous xref array (if any)
     *
     * @return array containing xref and trailer data
     *
     * @throws Exception if unknown PNG predictor detected
     */
    protected function decodeXrefStream($pdfData, $startxref, $xref = [])
    {
        // try to read Cross-Reference Stream
        $xrefobj = $this->getRawObject($pdfData, $startxref);
        $xrefcrs = $this->getIndirectObject($pdfData, $xref, $xrefobj[1], $startxref, true);
        if (!isset($xref['trailer']) or empty($xref['trailer'])) {
            // get only the last updated version
            $xref['trailer'] = [];
            $filltrailer = true;
        } else {
            $filltrailer = false;
        }
        if (!isset($xref['xref'])) {
            $xref['xref'] = [];
        }
        $valid_crs = false;
        $columns = 0;
        $sarr = $xrefcrs[0][1];
        if (!\is_array($sarr)) {
            $sarr = [];
        }

        $wb = [];

        foreach ($sarr as $k => $v) {
            if (
                ('/' == $v[0])
                && ('Type' == $v[1])
                && (
                    isset($sarr[($k + 1)])
                    && '/' == $sarr[($k + 1)][0]
                    && 'XRef' == $sarr[($k + 1)][1]
                )
            ) {
                $valid_crs = true;
            } elseif (('/' == $v[0]) and ('Index' == $v[1]) and (isset($sarr[($k + 1)]))) {
                // first object number in the subsection
                $index_first = (int) ($sarr[($k + 1)][1][0][1]);
            } elseif (('/' == $v[0]) and ('Prev' == $v[1]) and (isset($sarr[($k + 1)]) and ('numeric' == $sarr[($k + 1)][0]))) {
                // get previous xref offset
                $prevxref = (int) ($sarr[($k + 1)][1]);
            } elseif (('/' == $v[0]) and ('W' == $v[1]) and (isset($sarr[($k + 1)]))) {
                // number of bytes (in the decoded stream) of the corresponding field
                $wb[0] = (int) ($sarr[($k + 1)][1][0][1]);
                $wb[1] = (int) ($sarr[($k + 1)][1][1][1]);
                $wb[2] = (int) ($sarr[($k + 1)][1][2][1]);
            } elseif (('/' == $v[0]) and ('DecodeParms' == $v[1]) and (isset($sarr[($k + 1)][1]))) {
                $decpar = $sarr[($k + 1)][1];
                foreach ($decpar as $kdc => $vdc) {
                    if (
                        '/' == $vdc[0]
                        && 'Columns' == $vdc[1]
                        && (
                            isset($decpar[($kdc + 1)])
                            && 'numeric' == $decpar[($kdc + 1)][0]
                        )
                    ) {
                        $columns = (int) ($decpar[($kdc + 1)][1]);
                    } elseif (
                        '/' == $vdc[0]
                        && 'Predictor' == $vdc[1]
                        && (
                            isset($decpar[($kdc + 1)])
                            && 'numeric' == $decpar[($kdc + 1)][0]
                        )
                    ) {
                        $predictor = (int) ($decpar[($kdc + 1)][1]);
                    }
                }
            } elseif ($filltrailer) {
                if (('/' == $v[0]) and ('Size' == $v[1]) and (isset($sarr[($k + 1)]) and ('numeric' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['size'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) and ('Root' == $v[1]) and (isset($sarr[($k + 1)]) and ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['root'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) and ('Info' == $v[1]) and (isset($sarr[($k + 1)]) and ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['info'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) and ('Encrypt' == $v[1]) and (isset($sarr[($k + 1)]) and ('objref' == $sarr[($k + 1)][0]))) {
                    $xref['trailer']['encrypt'] = $sarr[($k + 1)][1];
                } elseif (('/' == $v[0]) and ('ID' == $v[1]) and (isset($sarr[($k + 1)]))) {
                    $xref['trailer']['id'] = [];
                    $xref['trailer']['id'][0] = $sarr[($k + 1)][1][0][1];
                    $xref['trailer']['id'][1] = $sarr[($k + 1)][1][1][1];
                }
            }
        }

        // decode data
        if ($valid_crs and isset($xrefcrs[1][3][0])) {
            // number of bytes in a row
            $rowlen = ($columns + 1);
            // convert the stream into an array of integers
            $sdata = unpack('C*', $xrefcrs[1][3][0]);
            // split the rows
            $sdata = array_chunk($sdata, $rowlen);
            // initialize decoded array
            $ddata = [];
            // initialize first row with zeros
            $prev_row = array_fill(0, $rowlen, 0);
            // for each row apply PNG unpredictor
            foreach ($sdata as $k => $row) {
                // initialize new row
                $ddata[$k] = [];
                // get PNG predictor value
                $predictor = (10 + $row[0]);
                // for each byte on the row
                for ($i = 1; $i <= $columns; ++$i) {
                    // new index
                    $j = ($i - 1);
                    $row_up = $prev_row[$j];
                    if (1 == $i) {
                        $row_left = 0;
                        $row_upleft = 0;
                    } else {
                        $row_left = $row[($i - 1)];
                        $row_upleft = $prev_row[($j - 1)];
                    }
                    switch ($predictor) {
                        case 10:  // PNG prediction (on encoding, PNG None on all rows)
                            $ddata[$k][$j] = $row[$i];
                            break;

                        case 11:  // PNG prediction (on encoding, PNG Sub on all rows)
                            $ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
                            break;

                        case 12:  // PNG prediction (on encoding, PNG Up on all rows)
                            $ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
                            break;

                        case 13:  // PNG prediction (on encoding, PNG Average on all rows)
                            $ddata[$k][$j] = (($row[$i] + (($row_left + $row_up) / 2)) & 0xff);
                            break;

                        case 14:  // PNG prediction (on encoding, PNG Paeth on all rows)
                            // initial estimate
                            $p = ($row_left + $row_up - $row_upleft);
                            // distances
                            $pa = abs($p - $row_left);
                            $pb = abs($p - $row_up);
                            $pc = abs($p - $row_upleft);
                            $pmin = min($pa, $pb, $pc);
                            // return minimum distance
                            switch ($pmin) {
                                case $pa:
                                    $ddata[$k][$j] = (($row[$i] + $row_left) & 0xff);
                                    break;

                                case $pb:
                                    $ddata[$k][$j] = (($row[$i] + $row_up) & 0xff);
                                    break;

                                case $pc:
                                    $ddata[$k][$j] = (($row[$i] + $row_upleft) & 0xff);
                                    break;
                            }
                            break;

                        default:  // PNG prediction (on encoding, PNG optimum)
                            throw new Exception('Unknown PNG predictor');
                    }
                }
                $prev_row = $ddata[$k];
            } // end for each row
            // complete decoding
            $sdata = [];
            // for every row
            foreach ($ddata as $k => $row) {
                // initialize new row
                $sdata[$k] = [0, 0, 0];
                if (0 == $wb[0]) {
                    // default type field
                    $sdata[$k][0] = 1;
                }
                $i = 0; // count bytes in the row
                // for every column
                for ($c = 0; $c < 3; ++$c) {
                    // for every byte on the column
                    for ($b = 0; $b < $wb[$c]; ++$b) {
                        if (isset($row[$i])) {
                            $sdata[$k][$c] += ($row[$i] << (($wb[$c] - 1 - $b) * 8));
                        }
                        ++$i;
                    }
                }
            }
            $ddata = [];
            // fill xref
            if (isset($index_first)) {
                $obj_num = $index_first;
            } else {
                $obj_num = 0;
            }
            foreach ($sdata as $k => $row) {
                switch ($row[0]) {
                    case 0:  // (f) linked list of free objects
                            break;

                    case 1:  // (n) objects that are in use but are not compressed
                            // create unique object index: [object number]_[generation number]
                            $index = $obj_num.'_'.$row[2];
                            // check if object already exist
                            if (!isset($xref['xref'][$index])) {
                                // store object offset position
                                $xref['xref'][$index] = $row[1];
                            }
                            break;

                    case 2:  // compressed objects
                            // $row[1] = object number of the object stream in which this object is stored
                            // $row[2] = index of this object within the object stream
                            $index = $row[1].'_0_'.$row[2];
                            $xref['xref'][$index] = -1;
                            break;

                    default:  // null objects
                            break;
                }
                ++$obj_num;
            }
        } // end decoding data
        if (isset($prevxref)) {
            // get previous xref
            $xref = $this->getXrefData($pdfData, $prevxref, $xref);
        }

        return $xref;
    }

    /**
     * Get content of indirect object.
     *
     * @param string $pdfData  PDF data
     * @param array  $xref
     * @param string $objRef   Object number and generation number separated by underscore character
     * @param int    $offset   Object offset
     * @param bool   $decoding If true decode streams
     *
     * @return array containing object data
     *
     * @throws Exception if invalid object reference found
     */
    protected function getIndirectObject($pdfData, $xref, $objRef, $offset = 0, $decoding = true)
    {
        /*
         * build indirect object header
         */
        // $objHeader = "[object number] [generation number] obj"
        $objRefArr = explode('_', $objRef);
        if (2 !== \count($objRefArr)) {
            throw new Exception('Invalid object reference for $obj.');
        }
        $objHeader = $objRefArr[0].' '.$objRefArr[1].' obj';

        /*
         * check if we are in position
         */
        // ignore whitespace characters at offset (NUL, HT, LF, FF, CR, SP)
        $offset += strspn($pdfData, "\0\t\n\f\r ", $offset);
        // ignore leading zeros for object number
        $offset += strspn($pdfData, '0', $offset);
        if (substr($pdfData, $offset, \strlen($objHeader)) !== $objHeader) {
            // an indirect reference to an undefined object shall be considered a reference to the null object
            return ['null', 'null', $offset];
        }

        /*
         * get content
         */
        // starting position of object content
        $offset += \strlen($objHeader);
        $objContentArr = [];
        $i = 0; // object main index
        do {
            $oldOffset = $offset;
            // get element
            $element = $this->getRawObject($pdfData, $offset);
            $offset = $element[2];
            // decode stream using stream's dictionary information
            if ($decoding && ('stream' === $element[0]) && (isset($objContentArr[($i - 1)][0])) && ('<<' === $objContentArr[($i - 1)][0])) {
                $element[3] = $this->decodeStream($pdfData, $xref, $objContentArr[($i - 1)][1], $element[1]);
            }
            $objContentArr[$i] = $element;
            ++$i;
        } while (('endobj' !== $element[0]) && ($offset !== $oldOffset));
        // remove closing delimiter
        array_pop($objContentArr);

        /*
         * return raw object content
         */
        return $objContentArr;
    }

    /**
     * Get the content of object, resolving indirect object reference if necessary.
     *
     * @param string $pdfData PDF data
     * @param array  $obj     Object value
     *
     * @return array containing object data
     *
     * @throws Exception
     */
    protected function getObjectVal($pdfData, $xref, $obj)
    {
        if ('objref' == $obj[0]) {
            // reference to indirect object
            if (isset($this->objects[$obj[1]])) {
                // this object has been already parsed
                return $this->objects[$obj[1]];
            } elseif (isset($xref[$obj[1]])) {
                // parse new object
                $this->objects[$obj[1]] = $this->getIndirectObject($pdfData, $xref, $obj[1], $xref[$obj[1]], false);

                return $this->objects[$obj[1]];
            }
        }

        return $obj;
    }

    /**
     * Get object type, raw value and offset to next object
     *
     * @param int $offset Object offset
     *
     * @return array containing object type, raw value and offset to next object
     */
    protected function getRawObject($pdfData, $offset = 0)
    {
        $objtype = ''; // object type to be returned
        $objval = ''; // object value to be returned

        /*
         * skip initial white space chars:
         *      \x00 null (NUL)
         *      \x09 horizontal tab (HT)
         *      \x0A line feed (LF)
         *      \x0C form feed (FF)
         *      \x0D carriage return (CR)
         *      \x20 space (SP)
         */
        $offset += strspn($pdfData, "\x00\x09\x0a\x0c\x0d\x20", $offset);

        // get first char
        $char = $pdfData[$offset];
        // get object type
        switch ($char) {
            case '%':  // \x25 PERCENT SIGN
                    // skip comment and search for next token
                    $next = strcspn($pdfData, "\r\n", $offset);
                    if ($next > 0) {
                        $offset += $next;

                        return $this->getRawObject($pdfData, $offset);
                    }
                    break;

            case '/':  // \x2F SOLIDUS
                    // name object
                    $objtype = $char;
                    ++$offset;
                    $pregResult = preg_match(
                        '/^([^\x00\x09\x0a\x0c\x0d\x20\s\x28\x29\x3c\x3e\x5b\x5d\x7b\x7d\x2f\x25]+)/',
                        substr($pdfData, $offset, 256),
                        $matches
                    );
                    if (1 == $pregResult) {
                        $objval = $matches[1]; // unescaped value
                        $offset += \strlen($objval);
                    }
                    break;

            case '(':   // \x28 LEFT PARENTHESIS
            case ')':  // \x29 RIGHT PARENTHESIS
                    // literal string object
                    $objtype = $char;
                    ++$offset;
                    $strpos = $offset;
                    if ('(' == $char) {
                        $open_bracket = 1;
                        while ($open_bracket > 0) {
                            if (!isset($pdfData[$strpos])) {
                                break;
                            }
                            $ch = $pdfData[$strpos];
                            switch ($ch) {
                                case '\\':  // REVERSE SOLIDUS (5Ch) (Backslash)
                                        // skip next character
                                        ++$strpos;
                                        break;

                                case '(':  // LEFT PARENHESIS (28h)
                                        ++$open_bracket;
                                        break;

                                case ')':  // RIGHT PARENTHESIS (29h)
                                        --$open_bracket;
                                        break;
                            }
                            ++$strpos;
                        }
                        $objval = substr($pdfData, $offset, ($strpos - $offset - 1));
                        $offset = $strpos;
                    }
                    break;

            case '[':   // \x5B LEFT SQUARE BRACKET
            case ']':  // \x5D RIGHT SQUARE BRACKET
                    // array object
                    $objtype = $char;
                    ++$offset;
                    if ('[' == $char) {
                        // get array content
                        $objval = [];
                        do {
                            // get element
                            $element = $this->getRawObject($pdfData, $offset);
                            $offset = $element[2];
                            $objval[] = $element;
                        } while (']' != $element[0]);
                        // remove closing delimiter
                        array_pop($objval);
                    }
                    break;

            case '<':  // \x3C LESS-THAN SIGN
            case '>':  // \x3E GREATER-THAN SIGN
                    if (isset($pdfData[($offset + 1)]) and ($pdfData[($offset + 1)] == $char)) {
                        // dictionary object
                        $objtype = $char.$char;
                        $offset += 2;
                        if ('<' == $char) {
                            // get array content
                            $objval = [];
                            do {
                                // get element
                                $element = $this->getRawObject($pdfData, $offset);
                                $offset = $element[2];
                                $objval[] = $element;
                            } while ('>>' != $element[0]);
                            // remove closing delimiter
                            array_pop($objval);
                        }
                    } else {
                        // hexadecimal string object
                        $objtype = $char;
                        ++$offset;
                        $pregResult = preg_match(
                            '/^([0-9A-Fa-f\x09\x0a\x0c\x0d\x20]+)>/iU',
                            substr($pdfData, $offset),
                            $matches
                        );
                        if (('<' == $char) && 1 == $pregResult) {
                            // remove white space characters
                            $objval = strtr($matches[1], "\x09\x0a\x0c\x0d\x20", '');
                            $offset += \strlen($matches[0]);
                        } elseif (false !== ($endpos = strpos($pdfData, '>', $offset))) {
                            $offset = $endpos + 1;
                        }
                    }
                    break;

            default:
                    if ('endobj' == substr($pdfData, $offset, 6)) {
                        // indirect object
                        $objtype = 'endobj';
                        $offset += 6;
                    } elseif ('null' == substr($pdfData, $offset, 4)) {
                        // null object
                        $objtype = 'null';
                        $offset += 4;
                        $objval = 'null';
                    } elseif ('true' == substr($pdfData, $offset, 4)) {
                        // boolean true object
                        $objtype = 'boolean';
                        $offset += 4;
                        $objval = 'true';
                    } elseif ('false' == substr($pdfData, $offset, 5)) {
                        // boolean false object
                        $objtype = 'boolean';
                        $offset += 5;
                        $objval = 'false';
                    } elseif ('stream' == substr($pdfData, $offset, 6)) {
                        // start stream object
                        $objtype = 'stream';
                        $offset += 6;
                        if (1 == preg_match('/^([\r]?[\n])/isU', substr($pdfData, $offset), $matches)) {
                            $offset += \strlen($matches[0]);
                            $pregResult = preg_match(
                                '/(endstream)[\x09\x0a\x0c\x0d\x20]/isU',
                                substr($pdfData, $offset),
                                $matches,
                                PREG_OFFSET_CAPTURE
                            );
                            if (1 == $pregResult) {
                                $objval = substr($pdfData, $offset, $matches[0][1]);
                                $offset += $matches[1][1];
                            }
                        }
                    } elseif ('endstream' == substr($pdfData, $offset, 9)) {
                        // end stream object
                        $objtype = 'endstream';
                        $offset += 9;
                    } elseif (1 == preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+R/iU', substr($pdfData, $offset, 33), $matches)) {
                        // indirect object reference
                        $objtype = 'objref';
                        $offset += \strlen($matches[0]);
                        $objval = (int) ($matches[1]).'_'.(int) ($matches[2]);
                    } elseif (1 == preg_match('/^([0-9]+)[\s]+([0-9]+)[\s]+obj/iU', substr($pdfData, $offset, 33), $matches)) {
                        // object start
                        $objtype = 'obj';
                        $objval = (int) ($matches[1]).'_'.(int) ($matches[2]);
                        $offset += \strlen($matches[0]);
                    } elseif (($numlen = strspn($pdfData, '+-.0123456789', $offset)) > 0) {
                        // numeric object
                        $objtype = 'numeric';
                        $objval = substr($pdfData, $offset, $numlen);
                        $offset += $numlen;
                    }
                    break;
        }

        return [$objtype, $objval, $offset];
    }

    /**
     * Get Cross-Reference (xref) table and trailer data from PDF document data.
     *
     * @param string $pdfData
     * @param int    $offset  xref offset (if know)
     * @param array  $xref    previous xref array (if any)
     *
     * @return array containing xref and trailer data
     *
     * @throws Exception if it was unable to find startxref
     * @throws Exception if it was unable to find xref
     */
    protected function getXrefData($pdfData, $offset = 0, $xref = [])
    {
        $startxrefPreg = preg_match(
            '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
            $pdfData,
            $matches,
            PREG_OFFSET_CAPTURE,
            $offset
        );

        if (0 == $offset) {
            // find last startxref
            $pregResult = preg_match_all(
                '/[\r\n]startxref[\s]*[\r\n]+([0-9]+)[\s]*[\r\n]+%%EOF/i',
                $pdfData, $matches,
                PREG_SET_ORDER,
                $offset
            );
            if (0 == $pregResult) {
                throw new Exception('Unable to find startxref');
            }
            $matches = array_pop($matches);
            $startxref = $matches[1];
        } elseif (strpos($pdfData, 'xref', $offset) == $offset) {
            // Already pointing at the xref table
            $startxref = $offset;
        } elseif (preg_match('/([0-9]+[\s][0-9]+[\s]obj)/i', $pdfData, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            // Cross-Reference Stream object
            $startxref = $offset;
        } elseif ($startxrefPreg) {
            // startxref found
            $startxref = $matches[1][0];
        } else {
            throw new Exception('Unable to find startxref');
        }

        if ($startxref > \strlen($pdfData)) {
            throw new Exception('Unable to find xref (PDF corrupted?)');
        }

        // check xref position
        if (strpos($pdfData, 'xref', $startxref) == $startxref) {
            // Cross-Reference
            $xref = $this->decodeXref($pdfData, $startxref, $xref);
        } else {
            // Cross-Reference Stream
            $xref = $this->decodeXrefStream($pdfData, $startxref, $xref);
        }
        if (empty($xref)) {
            throw new Exception('Unable to find xref');
        }

        return $xref;
    }

    /**
     * Parses PDF data and returns extracted data as array.
     *
     * @param string $data PDF data to parse
     *
     * @return array array of parsed PDF document objects
     *
     * @throws Exception if empty PDF data given
     * @throws Exception if PDF data missing %PDF header
     */
    public function parseData($data)
    {
        if (empty($data)) {
            throw new Exception('Empty PDF data given.');
        }
        // find the pdf header starting position
        if (false === ($trimpos = strpos($data, '%PDF-'))) {
            throw new Exception('Invalid PDF data: missing %PDF header.');
        }

        // get PDF content string
        $pdfData = substr($data, $trimpos);

        // get xref and trailer data
        $xref = $this->getXrefData($pdfData);

        // parse all document objects
        $objects = [];
        foreach ($xref['xref'] as $obj => $offset) {
            if (!isset($objects[$obj]) and ($offset > 0)) {
                // decode objects with positive offset
                $objects[$obj] = $this->getIndirectObject($pdfData, $xref, $obj, $offset, true);
            }
        }

        return [$xref, $objects];
    }
}
