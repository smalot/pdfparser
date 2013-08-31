<?php

namespace Smalot\PdfParser;

/**
 * Class Page
 * @package PdfParser
 */
class Page extends Object
{
    /**
     * @var Object
     */
    protected $resources = null;
    /**
     * @var Object
     */
    protected $contents = null;
    /**
     * @var Font[]
     */
    protected $fonts = null;

    /**
     * @return Object
     */
    public function getResources()
    {
        if (!is_null($this->resources)) {
            return $this->resources;
        }

        $resources = $this->get('Resources');

        /*var_dump($resources);
        die();*/

        return ($this->resources = $resources);
    }

    /**
     * @return null|Object
     */
    public function getContents()
    {
        if (!is_null($this->contents)) {
            return $this->contents;
        }

        if ($this->has('Contents')) {
            $contents = $this->get('Contents');

            if (!($contents instanceof Object)) {
                $contents = $this->getContent();
            }

            return ($this->contents = $contents);
        }

        return null;
    }

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (!is_null($this->fonts)) {
            return $this->fonts;
        }

        $resources = $this->getResources();
        if ($resources->getHeader()->has('Font')) {
            $fonts = $resources->getHeader()->get('Font')->getHeader()->getElements();
            $table = array();
            foreach ($fonts as $id => $font) {
                $id         = preg_replace('/[^0-9\.\-_]/', '', $id);
                $table[$id] = $font;
            }

            return ($this->fonts = $table);
        } else {
            return array();
        }
    }

    /**
     * @param string $id
     *
     * @return Font
     */
    public function getFont($id)
    {
        $fonts = $this->getFonts();
        $id    = preg_replace('/[^0-9\.\-_]/', '', $id);

        return $fonts[$id];
    }

    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        $contents = $this->getContents();
        //var_dump($contents);
        //die();

        if ($contents) {
            if (is_array($contents)) {
                $text = '';

                foreach ($contents as $content) {
                    $text .= $content->getText($this) . "\n";
                }

                return $text;
            } else {
                return $contents->getText($this);
            }
        } else {
            return '';
        }
    }
}
