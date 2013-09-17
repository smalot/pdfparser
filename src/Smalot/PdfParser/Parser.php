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

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementHexa;
use Smalot\PdfParser\Element\ElementName;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;
use Smalot\PdfParser\Element\ElementXRef;

/**
 * Class Parser
 *
 * @package Smalot\PdfParser
 */
class Parser
{
    /**
     * @var Object[]
     */
    protected $objects = array();

    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Parse PDF file
     *
     * @param string $filename
     *
     * @return Document
     */
    public function parseFile($filename)
    {
        $content = file_get_contents($filename);

        return $this->parseContent($content);
    }

    /**
     * Parse PDF content
     *
     * @param string $content
     *
     * @return Document
     */
    public function parseContent($content)
    {
        // Create structure using TCPDF Parser.
        $parser            = new \TCPDF_PARSER($content);
        list($xref, $data) = $parser->getParsedData();

//        var_dump($xref, $data);
//        die();

        // Create destination object.
        $document       = new Document();
        $this->objects  = array();

        foreach ($data as $id => $structure) {
            $object = $this->parseObject($id, $structure, $document);
            $this->objects[$id] = $object;
        }

//        foreach ($this->objects as $id => $object) {
//            echo $id . "\n";
//        }

        $document->setObjects($this->objects);

        /** @var Object $infos */
        $infos = $document->getObjectById($xref['trailer']['info']);
//        foreach ($infos->getHeader()->getElements() as $name => $value) {
//            echo $name . ': ' . $value . "\n";
//        }

        return $document;
    }

    /**
     * @param string   $id
     * @param array    $structure
     * @param Document $document
     *
     * @return Object
     */
    protected function parseObject(&$id, $structure, $document)
    {
        $header  = new Header(array(), $document);
        $content = '';

        foreach ($structure as $position => $part) {
            switch ($part[0]) {
                case '<<':
                    $header = $this->parseHeader($part[1], $document);
                    break;

                case 'stream':
                    $content = isset($part[3][0])?$part[3][0]:$part[1];
                    $match   = array();
                    if (preg_match('/^\s*(\d+\s+\d+)\s+(<<.*>>.*)$/s', $content, $match)) {
                        $object = Object::parse($document, $match[2]);
                        $this->objects[str_replace(' ', '_', $match[1])] = $object;
                    }
                    break;
            }
        }

        return Object::factory($document, $header, $content);
    }

    /**
     * @param array    $structure
     * @param Document $document
     *
     * @return Header
     * @throws \Exception
     */
    protected function parseHeader($structure, $document)
    {
        $elements = array();
        $count    = count($structure);

        for ($position=0 ; $position<$count; $position+=2) {
            $name  = $structure[$position][1];
            $type  = $structure[$position+1][0];
            $value = $structure[$position+1][1];

            $elements[$name] = $this->parseHeaderElement($type, $value, $document);
        }

        return new Header($elements, $document);
    }

    /**
     * @param $type
     * @param $value
     * @param $document
     *
     * @return Element|Header
     * @throws \Exception
     */
    protected function parseHeaderElement($type, $value, $document)
    {
        switch ($type) {
            case '<<':
                return $this->parseHeader($value, $document);

            case 'numeric':
                return new ElementNumeric($value, $document);

            case 'boolean':
                return new ElementBoolean($value, $document);

            case 'null':
                return new ElementNull($value, $document);

            case '(':
                if ($date = ElementDate::parse('(' . $value . ')', $document)) {
                    return $date;
                } else {
                    return ElementString::parse('(' . $value . ')', $document);
                }

            case '<':
                return new ElementHexa($value, $document);

            case '/':
                return ElementName::parse('/' . $value, $document);

            case 'ojbref':
                return new ElementXRef($value, $document);

            case '[':
                $values = array();

                foreach ($value as $sub_element) {
                    $sub_type  = $sub_element[0];
                    $sub_value = $sub_element[1];
                    $values[]  = $this->parseHeaderElement($sub_type, $sub_value, $document);
                }

                return new ElementArray($values, $document);

            default:
                throw new \Exception('Invalid type: "' . $type . '".');
        }
    }

//    /**
//     * Convert a PDF into text.
//     *
//     * @return string The extracted text from the PDF
//     */
//    protected static function extractText(Document $pdf)
//    {
//        $pages = $pdf->getPages();
//        $texts = array();
//
////        var_dump($pdf->getObjectById(1227)->getContent());
////        var_dump($pdf->getObjectById(1227)->loadTranslateTable());
////        die('test');
//
//        $details = $pdf->getDetails();
//        foreach ($details as $key => $value) {
//            echo $key . ': ' . $value . "\n";
//        }
//
//        return 'test';
//
//        return $pages[2]->getText();
//
//        foreach ($pages as $page) {
//            // Add a new text block if not empty.
//            if ($text = $page->getText()) {
//                $texts[] = $text;
//            }
//        }
//
//        return implode("\n\n", $texts);
//    }
}
