<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 *
 * @date    2017-01-03
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

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementXRef;
use Smalot\PdfParser\RawData\RawDataParser;

/**
 * Class Parser
 */
class Parser
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PDFObject[]
     */
    protected $objects = [];

    protected $rawDataParser;

    public function __construct($cfg = [], ?Config $config = null)
    {
        $this->config = $config ?: new Config();
        $this->rawDataParser = new RawDataParser($cfg, $this->config);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @throws \Exception
     */
    public function parseFile(string $filename): Document
    {
        $content = file_get_contents($filename);

        /*
         * 2018/06/20 @doganoo as multiple times a
         * users have complained that the parseFile()
         * method dies silently, it is an better option
         * to remove the error control operator (@) and
         * let the users know that the method throws an exception
         * by adding @throws tag to PHPDoc.
         *
         * See here for an example: https://github.com/smalot/pdfparser/issues/204
         */
        return $this->parseContent($content);
    }

    /**
     * @param string $content PDF content to parse
     *
     * @throws \Exception if secured PDF file was detected
     * @throws \Exception if no object list was found
     */
    public function parseContent(string $content): Document
    {
        // Create structure from raw data.
        list($xref, $data) = $this->rawDataParser->parseData($content);

        if (isset($xref['trailer']['encrypt']) && false === $this->config->getIgnoreEncryption()) {
            throw new \Exception('Secured pdf file are currently not supported.');
        }

        if (empty($data)) {
            throw new \Exception('Object list not found. Possible secured file.');
        }

        // Create destination object.
        $document = new Document();
        $this->objects = [];

        foreach ($data as $id => $structure) {
            $this->parseObject($id, $structure, $document);
            unset($data[$id]);
        }

        $document->setTrailer($this->parseTrailer($xref['trailer'], $document));
        $document->setObjects($this->objects);

        return $document;
    }

    protected function parseTrailer(array $structure, ?Document $document)
    {
        $trailer = [];

        foreach ($structure as $name => $values) {
            $name = ucfirst($name);

            if (is_numeric($values)) {
                $trailer[$name] = new ElementNumeric($values);
            } elseif (\is_array($values)) {
                $value = $this->parseTrailer($values, null);
                $trailer[$name] = new ElementArray($value, null);
            } elseif (false !== strpos($values, '_')) {
                $trailer[$name] = new ElementXRef($values, $document);
            } else {
                $trailer[$name] = $this->parseHeaderElement('(', $values, $document);
            }
        }

        return new Header($trailer, $document);
    }

    protected function parseObject(string $id, array $structure, ?Document $document)
    {
        $header = new Header([], $document);
        $headerStructure = [];
        $content = '';

        foreach ($structure as $position => $part) {
            if (\is_int($part)) {
                $part = [null, null];
            }
            switch ($part[0]) {
                case '[':
                    $elements = [];

                    foreach ($part[1] as $sub_element) {
                        $sub_type = $sub_element[0];
                        $sub_value = $sub_element[1];
                        $elements[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }

                    $header = new Header($elements, $document);
                    break;

                case '<<':
                    $headerStructure = $part[1];
                    $header = $this->parseHeader($part[1], $document);
                    break;

                case 'stream':
                    $content = isset($part[3][0]) ? $part[3][0] : $part[1];

                    if ($header->get('Type')->equals('ObjStm')) {
                        $numberOfObjects = $this->objectStreamInteger($headerStructure, 'N');
                        $firstObjectOffset = $this->objectStreamInteger($headerStructure, 'First');

                        if (null === $numberOfObjects || null === $firstObjectOffset) {
                            list($xrefs, $firstObjectOffset) = $this->parseObjectStreamIndexWithoutMetadata($content);
                            $numberOfObjects = \count($xrefs);
                        } else {
                            if ($firstObjectOffset > \strlen($content)) {
                                throw new \UnexpectedValueException('Object stream First offset exceeds its content length.');
                            }

                            if ($numberOfObjects > $firstObjectOffset) {
                                throw new \UnexpectedValueException('Object stream N exceeds its index length.');
                            }

                            $xrefs = $this->parseObjectStreamIndex(
                                substr($content, 0, $firstObjectOffset),
                                $numberOfObjects
                            );
                        }

                        $content = substr($content, $firstObjectOffset);
                        $table = [];

                        foreach ($xrefs as $xref) {
                            $id = $xref[0];
                            $position = $xref[1];

                            if ($position > \strlen($content)) {
                                throw new \UnexpectedValueException('Object stream object offset exceeds its content length.');
                            }

                            $table[$position] = $id;
                        }

                        if (\count($table) !== $numberOfObjects) {
                            throw new \UnexpectedValueException('Object stream contains duplicate object offsets.');
                        }

                        ksort($table);

                        $ids = array_values($table);
                        $positions = array_keys($table);

                        foreach ($positions as $index => $position) {
                            $id = $ids[$index].'_0';
                            $next_position = isset($positions[$index + 1]) ? $positions[$index + 1] : \strlen($content);
                            $sub_content = substr($content, $position, (int) $next_position - (int) $position);

                            $sub_header = Header::parse($sub_content, $document);
                            $object = PDFObject::factory($document, $sub_header, '', $this->config);
                            $this->objects[$id] = $object;
                        }

                        // It is not necessary to store this content.

                        return;
                    } elseif ($header->get('Type')->equals('Metadata')) {
                        // Attempt to parse XMP XML Metadata
                        $document->extractXMPMetadata($content);
                    }
                    break;

                default:
                    if ('null' != $part) {
                        $element = $this->parseHeaderElement($part[0], $part[1], $document);

                        if ($element) {
                            $header = new Header([$element], $document);
                        }
                    }
                    break;
            }
        }

        if (!isset($this->objects[$id])) {
            $this->objects[$id] = PDFObject::factory($document, $header, $content, $this->config);
        }
    }

    private function objectStreamInteger(array $headerStructure, string $name): ?int
    {
        $count = \count($headerStructure);

        for ($position = 0; $position + 1 < $count; $position += 2) {
            if ('/' !== $headerStructure[$position][0] || $name !== $headerStructure[$position][1]) {
                continue;
            }

            if ('numeric' !== $headerStructure[$position + 1][0]) {
                return null;
            }

            return $this->objectStreamToken($headerStructure[$position + 1][1]);
        }

        return null;
    }

    /**
     * @return array<int, array{0: int, 1: int}>
     */
    private function parseObjectStreamIndex(string $index, int $numberOfObjects): array
    {
        $xrefs = [];
        $cursor = 0;
        $length = \strlen($index);
        $this->skipObjectStreamWhitespace($index, $length, $cursor);

        for ($position = 0; $position < $numberOfObjects; ++$position) {
            $xrefs[] = [
                $this->readObjectStreamInteger($index, $length, $cursor),
                $this->readObjectStreamInteger($index, $length, $cursor),
            ];
        }

        if ($cursor !== $length) {
            throw new \UnexpectedValueException('Object stream index does not match its N value.');
        }

        return $xrefs;
    }

    /**
     * @return array{0: array<int, array{0: int, 1: int}>, 1: int}
     */
    private function parseObjectStreamIndexWithoutMetadata(string $content): array
    {
        $xrefs = [];
        $cursor = 0;
        $length = \strlen($content);
        $this->skipObjectStreamWhitespace($content, $length, $cursor);

        while (null !== ($objectId = $this->tryReadObjectStreamInteger($content, $length, $cursor))) {
            $offset = $this->tryReadObjectStreamInteger($content, $length, $cursor);

            if (null === $offset) {
                throw new \UnexpectedValueException('Object stream index does not contain complete object references.');
            }

            $xrefs[] = [$objectId, $offset];
        }

        return [$xrefs, $cursor];
    }

    private function readObjectStreamInteger(string $content, int $length, int &$cursor): int
    {
        $value = $this->tryReadObjectStreamInteger($content, $length, $cursor);

        if (null === $value) {
            throw new \UnexpectedValueException('Object stream index does not match its N value.');
        }

        return $value;
    }

    private function tryReadObjectStreamInteger(string $content, int $length, int &$cursor): ?int
    {
        if ($cursor >= $length || $content[$cursor] < '0' || $content[$cursor] > '9') {
            return null;
        }

        $start = $cursor;

        while ($cursor < $length && $content[$cursor] >= '0' && $content[$cursor] <= '9') {
            ++$cursor;
        }

        if ($cursor < $length && !$this->isObjectStreamWhitespace($content[$cursor])) {
            throw new \UnexpectedValueException('Object stream index values must be non-negative integers.');
        }

        $value = $this->objectStreamToken(substr($content, $start, $cursor - $start));
        $this->skipObjectStreamWhitespace($content, $length, $cursor);

        return $value;
    }

    private function skipObjectStreamWhitespace(string $content, int $length, int &$cursor): void
    {
        while ($cursor < $length && $this->isObjectStreamWhitespace($content[$cursor])) {
            ++$cursor;
        }
    }

    private function isObjectStreamWhitespace(string $character): bool
    {
        return "\0" === $character
            || "\t" === $character
            || "\n" === $character
            || "\f" === $character
            || "\r" === $character
            || ' ' === $character;
    }

    private function objectStreamToken(string $token): int
    {
        $normalized = ltrim($token, '0');
        $normalized = '' === $normalized ? '0' : $normalized;
        $maximum = (string) \PHP_INT_MAX;

        if (strspn($token, '0123456789') !== \strlen($token)
            || \strlen($normalized) > \strlen($maximum)
            || (\strlen($normalized) === \strlen($maximum) && strcmp($normalized, $maximum) > 0)
        ) {
            throw new \UnexpectedValueException('Object stream index values must be non-negative integers.');
        }

        return (int) $normalized;
    }

    /**
     * @throws \Exception
     */
    protected function parseHeader(array $structure, ?Document $document): Header
    {
        $elements = [];
        $count = \count($structure);

        for ($position = 0; $position < $count; $position += 2) {
            $name = $structure[$position][1];
            $type = $structure[$position + 1][0];
            $value = $structure[$position + 1][1];

            $elements[$name] = $this->parseHeaderElement($type, $value, $document);
        }

        return new Header($elements, $document);
    }

    /**
     * @param string|array $value
     *
     * @return Element|Header|null
     *
     * @throws \Exception
     */
    protected function parseHeaderElement(?string $type, $value, ?Document $document)
    {
        $valueIsEmpty = null == $value || '' == $value || false == $value;
        if (('<<' === $type || '>>' === $type) && $valueIsEmpty) {
            $value = [];
        }

        switch ($type) {
            case '<<':
            case '>>':
                $header = $this->parseHeader($value, $document);
                PDFObject::factory($document, $header, null, $this->config);

                return $header;

            case 'numeric':
                return new ElementNumeric($value);

            case 'boolean':
                return new ElementBoolean($value);

            case 'null':
                return new ElementNull();

            case '(':
                if ($date = ElementDate::parse('('.$value.')', $document)) {
                    return $date;
                }

                return ElementString::parse('('.$value.')', $document);

            case '<':
                return $this->parseHeaderElement('(', ElementHexa::decode($value), $document);

            case '/':
                return ElementName::parse('/'.$value, $document);

            case 'ojbref': // old mistake in tcpdf parser
            case 'objref':
                return new ElementXRef($value, $document);

            case '[':
                $values = [];

                if (\is_array($value)) {
                    foreach ($value as $sub_element) {
                        $sub_type = $sub_element[0];
                        $sub_value = $sub_element[1];
                        $values[] = $this->parseHeaderElement($sub_type, $sub_value, $document);
                    }
                }

                return new ElementArray($values, $document);

            case 'endstream':
            case 'obj': // I don't know what it means but got my project fixed.
            case '':
                // Nothing to do with.
                return null;

            default:
                throw new \Exception('Invalid type: "'.$type.'".');
        }
    }
}
