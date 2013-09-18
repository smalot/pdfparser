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

use Smalot\PdfParser\Element\ElementBoolean;
use Smalot\PdfParser\Element\ElementDate;
use Smalot\PdfParser\Element\ElementNumeric;
use Smalot\PdfParser\Element\ElementString;

/**
 * Technical references :
 * - http://www.mactech.com/articles/mactech/Vol.15/15.09/PDFIntro/index.html
 * - http://framework.zend.com/issues/secure/attachment/12512/Pdf.php
 * - http://www.php.net/manual/en/ref.pdf.php#74211
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin1Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin9Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/StandardEncoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/WinAnsiEncoding.pm
 *
 * Class Document
 *
 * @package Smalot\PdfParser
 */
class Document
{
    /**
     * @var Object[]
     */
    protected $objects = array();

    /**
     * @var array
     */
    protected $dictionary = array();

    /**
     * @var Header
     */
    protected $trailer = null;

    /**
     * @var array
     */
    protected $details = null;

    /**
     * @var bool
     */
    protected $encrypted = false;

    /**
     *
     */
    public function __construct()
    {
        $this->trailer = new Header(array(), $this);
    }

    /**
     *
     */
    public function init()
    {
        $this->buildDictionary();

        $this->buildDetails();

        // Propagate init to objects.
        foreach ($this->objects as $object) {
            $object->init();
        }
    }

    /**
     * Build dictionary based on type header field.
     */
    protected function buildDictionary()
    {
        // Build dictionary.
        $this->dictionary = array();

        foreach ($this->objects as $id => $object) {
            $type = $object->getHeader()->get('Type')->getContent();

            if (!empty($type)) {
                $this->dictionary[$type][$id] = $id;
            }
        }
    }

    /**
     * Build details array and set encrypted flag.
     */
    protected function buildDetails()
    {
        // Build details array.
        $details = array();

        // Set encrypted flag.
        $details['Encrypted'] = $this->trailer->has('Encrypt');

        // Extract document info
        if ($this->trailer->has('Info')) {
            /** @var Object $info */
            $info     = $this->trailer->get('Info');
            $elements = $info->getHeader()->getElements();

            foreach ($elements as $name => $element) {
                if ($element instanceof ElementDate) {
                    $element->setFormat('c');
                }
                $details[$name] = (string)$element;
            }
        }

        // Retrieve the page count
        try {
            $pages            = $this->getPages();
            $details['Pages'] = count($pages);
        } catch (\Exception $e) {
            $details['Pages'] = 0;
        }

        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param Object[] $objects
     */
    public function setObjects($objects = array())
    {
        $this->objects = (array)$objects;

        $this->init();
    }

    /**
     * @return Object[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @param string $id
     *
     * @return Object
     */
    public function getObjectById($id)
    {
        if (isset($this->objects[$id])) {
            return $this->objects[$id];
        } else {
            return null;
        }
    }

    /**
     * @param string $type
     * @param string $subtype
     *
     * @return Object[]
     */
    public function getObjectsByType($type, $subtype = null)
    {
        $objects = array();

        foreach ($this->objects as $id => $object) {
            if ($object->getHeader()->get('Type') == $type &&
                (is_null($subtype) || $object->getHeader()->get('Subtype') == $subtype)
            ) {
                $objects[$id] = $object;
            }
        }

        return $objects;
    }

    /**
     * @return \Object[]
     */
    public function getFonts()
    {
        return $this->getObjectsByType('Font');
    }

    /**
     * @return Page[]
     * @throws \Exception
     */
    public function getPages()
    {
        if (isset($this->dictionary['Catalog'])) {
            // Search for catalog to list pages.
            $id = reset($this->dictionary['Catalog']);

            /** @var Pages $object */
            $object = $this->objects[$id]->get('Pages');
            $pages  = $object->getPages(true);

            return $pages;
        } elseif (isset($this->dictionary['Pages'])) {
            // Search for pages to list kids.
            $pages = array();

            /** @var Pages[] $objects */
            $objects = $this->getObjectsByType('Pages');
            foreach ($objects as $object) {
                $pages = array_merge($pages, $object->getPages(true));
            }

            return $pages;
        } elseif (isset($this->dictionary['Page'])) {
            // Search for 'page' (unordered pages).
            $pages = $this->getObjectsByType('Page');

            return array_values($pages);
        } else {
            throw new \Exception('Missing catalog.');
        }
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $texts = array();
        $pages = $this->getPages();

        foreach ($pages as $index => $page) {
            if ($text = trim($page->getText())) {
                $texts[] = $text;
            }
        }

        return implode("\n\n", $texts);
    }

    /**
     * @param Header $header
     */
    public function setTrailer(Header $trailer)
    {
        $this->trailer = $trailer;
    }

    /**
     * @param array $details
     */
    public function setDetails(array $details)
    {
        $this->details = $details;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

//    /**
//     * @return bool
//     */
//    public function isEncrypted()
//    {
//        return $this->encrypted;
//    }
//
//    /**
//     * @param string $content
//     *
//     * @return array
//     */
//    protected static function detectInfo($content)
//    {
//        $match = array();
//
//        // Detect pdf version.
//        if (!preg_match('/^\s*%PDF-([0-9\.]*)/s', $content, $match)) {
//            throw new \Exception('Invalid PDF: missing pdf version.');
//        } else {
//            $version = $match[1];
//        }
//
//        // Detect if pdf is linearized.
//        if (!preg_match('/(<<.*)/s', $content, $match)) {
//            throw new \Exception('Invalid PDF: missing first object.');
//        } else {
//            $document   = new self();
//            $header     = Header::parse($match[1], $document);
//            $linearized = $header->has('Linearized');
//        }
//
//        // Detect if pdf is encrypted.
/*        if (preg_match('/trailer\s*<<.*?(\/Encrypt\s+\d+\s+\d+\s+R).*?>>\s+startxref/s', $content, $match)) {*/
//            $secured = true;
//        } else {
//            $secured = false;
//        }
//
//        return array(
//            'version'    => $version,
//            'linearized' => $linearized,
//            'secured'    => $secured,
//        );
//    }

//    /**
//     * @param $filename
//     *
//     * @return Document
//     * @throws \Exception
//     */
//    public static function parseFile($filename)
//    {
//        $content = file_get_contents($filename);
//
//        return self::parseContent($content);
//
//        $infos = self::detectInfo($content);
//
//        if ($infos['linearized']) {
//            // Delinearize pdf file ?
//        }
//
//        // Search for 'startxref' position.
//        $handle = @fopen($filename, 'rb');
//        if (!$handle) {
//            throw new \Exception('Unable to read file.');
//        }
//
//        fseek($handle, 0, SEEK_SET);
//
//        try {
//            $position_startxref = 0;
//            while (($line = fgets($handle)) !== false) {
//                if (trim($line) == 'startxref') {
//                    $position_startxref = intval(fgets($handle));
//                    break;
//                }
//            }
//
//            if (!$position_startxref) {
//                throw new \Exception('Missing "startxref" tag.');
//            }
//
//            fseek($handle, $position_startxref, SEEK_SET);
//            $entries  = array();
//            $next_id  = 0;
//            $document = new self();
//
//            while (($line = fgets($handle)) !== false) {
//                $line = trim($line);
//                if ($line == 'xref' || $line == '') {
//                    continue;
//                } elseif ($line == 'trailer') {
//                    $trailer = fread($handle, 1000);
//                    $header  = Header::parse($trailer, $document);
//                    $document->setTrailer($header);
//                    break;
//                }
//
//                if (preg_match('/^\d+\s+\d+$/', $line)) {
//                    list($next_id,) = explode(' ', $line);
//                } elseif (preg_match('/^\d+\s+\d+\s+(n|f)$/', $line)) {
//                    list($offset, $generation, $in_use) = explode(' ', $line);
//                    $offset     = intval(ltrim($offset, '0'));
//                    $generation = intval(ltrim($generation, '0'));
//                    $in_use     = ($in_use == 'n');
//
//                    if ($next_id && $offset && $in_use) {
//                        $entries[$offset] = array(
//                            'id'         => $next_id,
//                            'offset'     => $offset,
//                            'generation' => $generation,
//                            'in_use'     => $in_use,
//                        );
//                    }
//
//                    $next_id++;
//                }
//            }
//
//            $entries[] = array(
//                'id'         => '',
//                'offset'     => $position_startxref,
//                'generation' => 0,
//                'in_use'     => true,
//            );
//
//            ksort($entries, SORT_NUMERIC);
//
//            $entries  = array_values($entries);
//            $objects  = array();
//
//            $count = count($entries) - 1;
//            for ($i = 0; $i < $count; $i++) {
//                $offset      = $entries[$i]['offset'];
//                $next_offset = $entries[$i + 1]['offset'];
//
//                fseek($handle, $offset, SEEK_SET);
//                $sub_content = fread($handle, $next_offset - $offset);
//                //            var_dump($content);
//
////                echo '---------------------------' . "\n";
////                echo '#' . $entries[$i]['id'] . "\n";
//
//                if (preg_match('/^\d+\s+\d+\s+obj\s*(?<data>.*)[\n\r]{0,2}endobj\s*$/s', $sub_content, $match)) {
//                    //                echo $entries[$i]['id'] . ' 0 obj' . "\n";
//                    $objects[$entries[$i]['id']] = Object::parse($document, $match['data']);
//                    //                echo '---------------------------' . "\n";
//                } elseif (preg_match('/^\d+\s+\d+\s+obj\s*(?<data>.*?)(\s*)$/s', $sub_content, $match)) {
//                    //                echo $entries[$i]['id'] . ' 0 obj' . "\n";
//                    $objects[$entries[$i]['id']] = Object::parse($document, $match['data']);
//                    //                echo '---------------------------' . "\n";
//                } else {
////                    echo $entries[$i]['id'] . ' 0 obj' . " ()\n";
//                    throw new \Exception('Invalid object declaration.');
//                }
//            }
//
//            $document->setObjects($objects);
//
//            return $document;
//        } catch (\Exception $e) {
//            var_dump($e);
//            trigger_error($e->getMessage());
//
//            // Fallback on raw content parsing.
//            return self::parseContent($content);
//        }
//    }
//
//    /**
//     * This method extract object from document using regular
//     * expressions. This is useful in case of missing xref section
//     * or invalid xref references.
//     *
//     * @param string $content
//     *
//     * @return Document
//     */
//    public static function parseContent($content)
//    {
//        $document = new self();
//        $infos    = self::detectInfo($content);
//
//        if ($infos['secured']) {
//            throw new \Exception('Secured PDF Files are not currently supported.');
//        }
//
//        $objects = array();
//        $regexp  = '/([\n\r]{1,2}\d+\s+\d+\s+obj)/s';
//        $parts   = preg_split($regexp, "\n" . $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
//        $id      = 0;
//
//        // Extract objects from content.
//        foreach ($parts as $part) {
////            echo "-------------------------------\n";
//            if (preg_match('/^([\n\r]{1,2}\d+\s+\d+\s+obj)$/s', $part)) {
//                if ($id != 0) {
//                    // In case of empty object content (strange situation).
//                    $objects[$id] = Object::parse($document, '');
//                }
//                $id = trim($part);
//            } elseif ($id != 0) {
////                echo 'object#' . $id . "\n";
//                // Remove trailing 'endobj'.
//                $part         = preg_replace('/endobj\s*$/s', '', $part);
//                $objects[$id] = Object::parse($document, $part);
//                $id           = 0;
//            }
//        }
//
//        // Extract first trailer content.
//        if (!preg_match('/[\n\r]+trailer(.*?)startxref[\n\r]+/s', $content, $match)) {
//            throw new \Exception('Missing trailer.');
//        } else {
//            $trailer = Header::parse($match[1], $document);
//            $document->setTrailer($trailer);
//        }
//
//        $document->setObjects($objects);
//
//        return $document;
//    }
}
