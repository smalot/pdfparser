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
    protected $objects;

    /**
     * @var array
     */
    protected $dictionary = array();

    /**
     *
     */
    public function __construct()
    {
        $this->objects = array();
    }

    /**
     * @param Object[] $objects
     */
    public function setObjects($objects)
    {
        foreach ($objects as $id => $object) {
            $type = $object->getHeader()->get('Type')->getContent();

            if (!empty($type)) {
                $this->dictionary[$type][$id] = $id;
            }
        }

        $this->objects = (array)$objects;
    }

    /**
     * @return Object[]
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * @return array
     */
    public function getDictionary()
    {
        return $this->dictionary;
    }

    /**
     * @param $id
     *
     * @return null|Object
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
     * @return Page[]
     * @throws \Exception
     */
    public function getPages()
    {
        if (isset($this->dictionary['Catalog'])) {
            $id = reset($this->dictionary['Catalog']);

            /** @var Pages $object */
            $object = $this->objects[$id]->get('Pages');
            $pages  = $object->getPages(true);

            return $pages;
        } elseif (isset($this->dictionary['Pages'])) {
            $pages = array();

            /** @var Pages[] $objects */
            $objects = $this->getObjectsByType('Pages');
            foreach ($objects as $object) {
                $pages = array_merge($pages, $object->getPages(true));
            }

            return $pages;
        } elseif (isset($this->dictionary['Page'])) {
            $pages = $this->getObjectsByType('Page');

            return array_values($pages);
        } else {
            throw new \Exception('Missing catalog.');
        }
    }

    /**
     * @param $filename
     *
     * @return Document
     * @throws \Exception
     */
    public static function parseFile($filename)
    {
        // Search for 'startxref' position.
        $handle = @fopen($filename, 'rb');
        if (!$handle) {
            throw new \Exception('Unable to read file.');
        }

        try {
            $position_startxref = 0;
            while (($line = fgets($handle)) !== false) {
                if (trim($line) == 'startxref') {
                    $position_startxref = intval(fgets($handle));
                    break;
                }
            }

            if (!$position_startxref) {
                throw new \Exception('Missing "startxref" tag.');
            }

            fseek($handle, $position_startxref, SEEK_SET);
            $entries = array();
            $next_id = 0;
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line == 'xref' || $line == '') {
                    continue;
                } elseif ($line == 'trailer') {
                    break;
                }

                if (preg_match('/^\d+\s+\d+$/', $line)) {
                    list($next_id,) = explode(' ', $line);
                } elseif (preg_match('/^\d+\s+\d+\s+(n|f)$/', $line)) {
                    list($offset, $generation, $in_use) = explode(' ', $line);
                    $offset     = intval(ltrim($offset, '0'));
                    $generation = intval(ltrim($generation, '0'));
                    $in_use     = ($in_use == 'n');

                    if ($next_id && $offset && $in_use) {
                        $entries[$offset] = array(
                            'id'         => $next_id,
                            'offset'     => $offset,
                            'generation' => $generation,
                            'in_use'     => $in_use,
                        );
                    }

                    $next_id++;
                }
            }

            $entries[] = array(
                'id'         => '',
                'offset'     => $position_startxref,
                'generation' => 0,
                'in_use'     => true,
            );

            ksort($entries, SORT_NUMERIC);

            $entries  = array_values($entries);
            $document = new self();
            $objects  = array();

            $count = count($entries) - 1;
            for ($i = 0; $i < $count; $i++) {
                $offset      = $entries[$i]['offset'];
                $next_offset = $entries[$i + 1]['offset'];

                fseek($handle, $offset, SEEK_SET);
                $content = fread($handle, $next_offset - $offset);
                //            var_dump($content);

//                echo '---------------------------' . "\n";
//                echo '#' . $entries[$i]['id'] . "\n";

                if (preg_match('/^\d+\s+\d+\s+obj\s*(?<data>.*)[\n\r]{0,2}endobj\s*$/s', $content, $match)) {
                    //                echo $entries[$i]['id'] . ' 0 obj' . "\n";
                    $objects[$entries[$i]['id']] = Object::parse($document, $match['data']);
                    //                echo '---------------------------' . "\n";
                } elseif (preg_match('/^\d+\s+\d+\s+obj\s*(?<data>.*?)(\s*)$/s', $content, $match)) {
                    //                echo $entries[$i]['id'] . ' 0 obj' . "\n";
                    $objects[$entries[$i]['id']] = Object::parse($document, $match['data']);
                    //                echo '---------------------------' . "\n";
                } else {
//                    echo $entries[$i]['id'] . ' 0 obj' . " ()\n";
                    throw new \Exception('Invalid object declaration.');
                }
            }

            $document->setObjects($objects);

            return $document;
        } catch (\Exception $e) {
//            trigger_error($e->getMessage());
            $content = file_get_contents($filename);

            return self::parseContent($content);
        }
    }

    /**
     * This method extract object from document using regular
     * expressions. This is useful in case of missing xref section
     * or invalid xref references.
     *
     * @param string $content
     *
     * @return Document
     */
    public static function parseContent($content)
    {
        $document = new self();
        $objects  = array();
        $regexp   = '/([\n\r]{1,2}\d+\s+\d+\s+obj)/s';
        $parts    = preg_split($regexp, "\n" . $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $id       = 0;

        // Extract objects from content.
        foreach ($parts as $part) {
            if (preg_match('/^([\n\r]{1,2}\d+\s+\d+\s+obj)$/s', $part)) {
                if ($id != 0) {
                    // In case of empty object content (strange situation).
                    $objects[$id] = Object::parse($document, '');
                }
                $id = intval(trim($part));
            } elseif ($id != 0) {
                // Remove trailing 'endobj'.
                $part         = preg_replace('/endobj\s*$/s', '', $part);
                $objects[$id] = Object::parse($document, $part);
                $id           = 0;
            }
        }

        $document->setObjects($objects);

        return $document;
    }
}
