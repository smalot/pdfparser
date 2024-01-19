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

class Info
{
    protected $docId;
    protected $metaData;
    protected $ok = false;

    protected $fileKeyLength = 0;
    protected $encAlgorithm = null;
    protected $streamFilter = "";
    protected $stringFilter = "";
    protected $cfLength = 0;


    function __construct(array $rawMetadata, array $idArr)
    {
        /** @var
         * Associative array indexed by $this->metaData key, mapping to arrays:
         *   [ <index:string>, <numeric:bool> ]
         */
        $metadataTranslation = [
            'version' =>  [ 'V', true ],
            'revision' => [ 'R', true ],
            'length' =>   [ 'Length', true ],
            'ownerKey' => [ 'O', false ],
            'userKey' =>  [ 'U', false ],
            'ownerEnc' => [ 'OE', false ],
            'userEnc' =>  [ 'UE', false ],
            'perms' =>    [ 'P', true ]
        ];

        $this->metadata = ['encryptMetadata' => true];
        // $rawMetadata is an array of one value being an array representing a PDF list
        $headerArr = $rawMetadata[0];
        if (\count($headerArr) == 3 && $headerArr[0] == '<<') {
            $headerDic = $headerArr[1];
        } else {
            throw new SyntaxError("Missing encryption header");
        }
        foreach ($metadataTranslation as $key => $info) {
            if ($info[1]) {
                $this->metadata[$key] = (int)\Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, $info[0], 'numeric');
            } else {
                // First look for a raw string
                $val = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, $info[0], '(', false);
                if (false !== $val) {
                    $this->metadata[$key] = $val;
                } else {
                    // Then look for a hex string
                    $val = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, $info[0], '<');
                    $this->metadata[$key] = \hex2bin($val);
                }
            }
        }

        // This should be an array
        try {
            $this->docID = $this->decodeDocID($idArr);
        }
        catch (\TypeError $e) {
            // or a scalar value, which is a spec breach
            $this->docID = "";
        }

        if ($this->metadata['version'] != 0 && $this->metadata['revision'] != 0
            && $this->metadata['perms'] != 0
            && is_string($this->metadata['ownerKey']) && is_string($this->metadata['userKey'])) {
            if (($this->metadata['revision'] <= 4 && strlen($this->metadata['ownerKey']) == 32 && strlen($this->metadata['userKey']) == 32)
                || (($this->metadata['revision'] == 5 || $this->metadata['revision'] == 6)
                    // the spec says 48 bytes, but Acrobat pads them out longer
                    && strlen($this->metadata['ownerKey']) >= 48 && strlen($this->metadata['userKey']) >= 48
                    && is_string($this->metadata['ownerEnc']) && strlen($this->metadata['ownerEnc']) == 32 && is_string($this->metadata['userEnc'])
                    && strlen($this->metadata['userEnc']) == 32)) {
                $this->encAlgorithm = 'RC4';
                // revision 2 forces a 40-bit key - some buggy PDF generators
                // set the Length value incorrectly
                if ($this->metadata['revision'] == 2 || $this->metadata['length'] == 0) {
                    $this->fileKeyLength = 5;
                } else {
                    $this->fileKeyLength = $this->metadata['length'] / 8;
                }
                $this->metadata['encryptMetadata'] = true;
                //~ this currently only handles a subset of crypt filter functionality
                //~ (in particular, it ignores the EFF entry in $headerDic, and
                //~ doesn't handle the case where StmF, StrF, and EFF are not all the
                //~ same)
                if (($this->metadata['version'] == 4 || $this->metadata['version'] == 5) && ($this->metadata['revision'] == 4 || $this->metadata['revision'] == 5 || $this->metadata['revision'] == 6)) {
                    $cryptFiltersDic = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, 'CF', '<<');
                    $this->metadata['streamFilter'] = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, 'StmF', '/');
                    $this->metadata['stringFilter'] = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, 'StrF', '/');
                    if (!empty($cryptFiltersDic) && is_string($this->metadata['streamFilter']) && is_string($this->metadata['stringFilter']) && $this->metadata['streamFilter'] == $this->metadata['stringFilter']) {
                        if ($this->metadata['streamFilter'] == "Identity") {
                            // no encryption on streams or strings
                            $this->metadata['version'] = $this->metadata['revision'] = -1;
                        } else {
                            // Find required crypt filter and its crypt filter method
                            // and update metadata accordingly
                            $cryptFilterInfoDic = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($cryptFiltersDic, $this->metadata['streamFilter'], '<<');
                            $method = \Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($cryptFilterInfoDic, 'CFM', '/');
                            switch ($method) {
                                case 'V2':
                                    $this->metadata['version'] = 2;
                                    $this->metadata['revision'] = 3;
                                    $this->metadata['cfLength'] = (int)\Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($cryptFilterInfoDic, 'Length', 'numeric', -1);
                                    if ($this->metadata['cfLength'] != 0) {
                                        //~ according to the spec, this should be cfLength / 8
                                        $this->fileKeyLength = $this->metadata['cfLength'];
                                    }
                                    break;

                                case 'AESV2':
                                    $this->metadata['version'] = 2;
                                    $this->metadata['revision'] = 3;
                                    $this->encAlgorithm = 'AES';
                                    $this->metadata['cfLength'] = (int)\Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($cryptFilterInfoDic, 'Length', 'numeric', -1);
                                    if ($this->metadata['cfLength'] != 0) {
                                        //~ according to the spec, this should be cfLength / 8
                                        $this->fileKeyLength = $this->metadata['cfLength'];
                                    }
                                    break;

                                case 'AESV3':
                                    $this->metadata['version'] = 5;
                                    // let $this->metadata['revision'] be 5 or 6
                                    $this->encAlgorithm = 'AES256';
                                    $this->metadata['cfLength'] = (int)\Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($cryptFilterInfoDic, 'Length', 'numeric', -1);
                                    if ($this->metadata['cfLength'] != 0) {
                                        //~ according to the spec, this should be cfLengthArr / 8
                                        $this->fileKeyLength = $this->metadata['cfLength'];
                                    }
                                    break;

                                default:
                                    throw new SyntaxError("Unknown CFM '$method'");
                            }
                        }
                    }
                    $this->metadata['encryptMetadata'] = (\Smalot\PdfParser\RawData\RawDataParser::getHeaderValue($headerDic, 'EncryptMetadata', 'boolean') === "true");
                }
                if ($this->metadata['version'] >= 1 && $this->metadata['version'] <= 2 && $this->metadata['revision'] >= 2 && $this->metadata['revision'] <= 3) {
                    if ($this->fileKeyLength > 16 || $this->fileKeyLength < 0) {
                        $this->fileKeyLength = 16;
                    }
                    $this->ok = true;
                } elseif ($this->metadata['version'] == 5 && ($this->metadata['revision'] == 5 || $this->metadata['revision'] == 6)) {
                    if (is_string($this->metadata['ownerEnc']) && is_string($this->metadata['userEnc'])) {
                        if ($this->fileKeyLength > 32 || $this->fileKeyLength < 0) {
                            $this->fileKeyLength = 32;
                        }
                        $this->ok = true;
                    } else {
                        throw new SyntaxError("Weird encryption owner/user info");
                    }
                } elseif (!($this->version == -1 && $this->revision == -1)) {
                    throw new Unimplemented("Unsupported version/revision (%d/%d) of Standard security handler", $this->version, $this->revision);
                }
            } else {
                throw new SyntaxError("Invalid encryption key length");
            }
        } else {
            throw new SyntaxError("Weird encryption info");
        }
    }


    /**
     * Get an element from the array of IDs and convert it from hex.
     *
     * @return a binary string
     */
    protected function decodeDocID(array $idArr)
    {
        // If multiple elements, assume that the first one is correct
        $result = \hex2bin($idArr[0]);
        if ($result === false)
        {
            throw new SyntaxError("Can't decode DocID");
        }
        return $result;
    }


    public function getVersion()
    {
        return $this->metadata['version'];
    }


    public function getRevision()
    {
        return $this->metadata['revision'];
    }


    public function getLength()
    {
        return $this->metadata['length'];
    }


    public function getOwnerKey()
    {
        return $this->metadata['ownerKey'];
    }


    public function getUserKey()
    {
        return $this->metadata['userKey'];
    }


    public function getOwnerEnc()
    {
        return $this->metadata['ownerEnc'];
    }


    public function getUserEnc()
    {
        return $this->metadata['userEnc'];
    }


    public function getPerms()
    {
        return $this->metadata['perms'];
    }


    public function getEncryptMetadata()
    {
        return $this->metadata['encryptMetadata'];
    }


    public function getDocID()
    {
        return $this->docID;
    }


    public function getEncAlgorithm()
    {
        return $this->encAlgorithm;
    }


    public function getFileKeyLength()
    {
        return $this->fileKeyLength;
    }
}
