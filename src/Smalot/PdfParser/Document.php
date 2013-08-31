<?php

namespace Smalot\PdfParser;

/**
 * @file
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2013-08-08
 * @license GPL-3.0
 *
 * References :
 * - http://www.mactech.com/articles/mactech/Vol.15/15.09/PDFIntro/index.html
 * - http://framework.zend.com/issues/secure/attachment/12512/Pdf.php
 * - http://www.php.net/manual/en/ref.pdf.php#74211
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin1Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/ISOLatin9Encoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/StandardEncoding.pm
 * - http://cpansearch.perl.org/src/JV/PostScript-Font-1.10.02/lib/PostScript/WinAnsiEncoding.pm
 *
 * Class Parser
 * @package PdfParser
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

            return $pages;
        } else {
            throw new \Exception('Missing catalog.');
        }
    }

    /**
     * @param $filename
     *
     * @return Document
     */
    public static function parseFile($filename)
    {
        // Search 'startxref' position.
        $handle = fopen($filename, 'rb');
        if (!$handle) {
            throw new \Exception('Unable to read file.');
        }

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

                if ($next_id && $offset) {
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

            if (preg_match('/^\d+\s+\d+\s+obj\s*(?<data>.*?)(endobj)?\s*$/s', $content, $match)) {
                echo $entries[$i]['id'] . ' 0 obj' . "\n";
                $objects[$entries[$i]['id']] = Object::parse($document, $match['data']);
                echo '---------------------------' . "\n";
            } else {
                var_dump($content);
                throw new \Exception('Object not found.');
            }
        }

        $document->setObjects($objects);

        return $document;
    }

    /**
     * @param $content
     *
     * @return Document
     */
    public static function parseContent($content)
    {
        $regexp  = '/(?<id>[0-9]+\s+[0-9]+\s+obj(\s+|<<))(?<data>.*?)(endobj\s+)/s';
        $matches = array();

        preg_match_all($regexp, $content, $matches);
        $data    = $matches['data'];
        $objects = array();

        $document = new self();

        foreach ($data as $key => $object) {
            $id = intval(trim($matches['id'][$key]));
            //echo $id . ' 0 obj' . "\n";
            $objects[$id] = Object::parse($document, $object);
            //echo 'type detected: ' . $objects[$id]->get('Type')->getContent() . "\n";
            //echo '------------------------------------------' . "\n";
        }

        $document->setObjects($objects);

        return $document;
    }
}
