<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
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
 *
 */

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Element\ElementXRef;
use Smalot\PdfParser\Element\ElementNull;

/**
 * Class Page
 *
 * @package Smalot\PdfParser
 */
class Page extends PDFObject
{
    /**
     * @var Font[]
     */
    protected $fonts = null;

    /**
     * @var PDFObject[]
     */
    protected $xobjects = null;

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (!is_null($this->fonts)) {
            return $this->fonts;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('Font')) {

            if ($resources->get('Font') instanceof Header) {
                $fonts = $resources->get('Font')->getElements();
            } else {
                $fonts = $resources->get('Font')->getHeader()->getElements();
            }

            $table = array();

            foreach ($fonts as $id => $font) {
                if ($font instanceof Font) {
                    $table[$id] = $font;

                    // Store too on cleaned id value (only numeric)
                    $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                    if ($id != '') {
                        $table[$id] = $font;
                    }
                }
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

        if (isset($fonts[$id])) {
            return $fonts[$id];
        } else {
            $id = preg_replace('/[^0-9\.\-_]/', '', $id);

            if (isset($fonts[$id])) {
                return $fonts[$id];
            } else {
                return null;
            }
        }
    }

    /**
     * Support for XObject
     *
     * @return PDFObject[]
     */
    public function getXObjects()
    {
        if (!is_null($this->xobjects)) {
            return $this->xobjects;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('XObject')) {

            if ($resources->get('XObject') instanceof Header) {
                $xobjects = $resources->get('XObject')->getElements();
            } else {
                $xobjects = $resources->get('XObject')->getHeader()->getElements();
            }

            $table = array();

            foreach ($xobjects as $id => $xobject) {
                $table[$id] = $xobject;

                // Store too on cleaned id value (only numeric)
                $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                if ($id != '') {
                    $table[$id] = $xobject;
                }
            }

            return ($this->xobjects = $table);
        } else {
            return array();
        }
    }

    /**
     * @param string $id
     *
     * @return PDFObject
     */
    public function getXObject($id)
    {
        $xobjects = $this->getXObjects();

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        } else {
            return null;
            /*$id = preg_replace('/[^0-9\.\-_]/', '', $id);

            if (isset($xobjects[$id])) {
                return $xobjects[$id];
            } else {
                return null;
            }*/
        }
    }

    /**
     * @param Page
     *
     * @return string
     */
    public function getText(Page $page = null)
    {
        if ($contents = $this->get('Contents')) {

            if ($contents instanceof ElementMissing) {
                return '';
			} elseif ($contents instanceof ElementNull) {
				return '';
            } elseif ($contents instanceof PDFObject) {
                $elements = $contents->getHeader()->getElements();

                if (is_numeric(key($elements))) {
                    $new_content = '';

                    foreach ($elements as $element) {
                        if ($element instanceof ElementXRef) {
                            $new_content .= $element->getObject()->getContent();
                        } else {
                            $new_content .= $element->getContent();
                        }
                    }

                    $header   = new Header(array(), $this->document);
                    $contents = new PDFObject($this->document, $header, $new_content);
                }
            } elseif ($contents instanceof ElementArray) {
                // Create a virtual global content.
                $new_content = '';

                foreach ($contents->getContent() as $content) {
                    $new_content .= $content->getContent() . "\n";
                }

                $header   = new Header(array(), $this->document);
                $contents = new PDFObject($this->document, $header, $new_content);
            }

            return $contents->getText($this);
        }

        return '';
    }

	/**
	 * @param Page
	 *
	 * @return array
	 */
	public function getTextArray(Page $page = null)
	{
		if ($contents = $this->get('Contents')) {

			if ($contents instanceof ElementMissing) {
				return array();
			} elseif ($contents instanceof ElementNull) {
				return array();
			} elseif ($contents instanceof PDFObject) {
				$elements = $contents->getHeader()->getElements();

				if (is_numeric(key($elements))) {
					$new_content = '';

					/** @var PDFObject $element */
					foreach ($elements as $element) {
						if ($element instanceof ElementXRef) {
							$new_content .= $element->getObject()->getContent();
						} else {
							$new_content .= $element->getContent();
						}
					}

					$header   = new Header(array(), $this->document);
					$contents = new PDFObject($this->document, $header, $new_content);
				}
			} elseif ($contents instanceof ElementArray) {
				// Create a virtual global content.
				$new_content = '';

				/** @var PDFObject $content */
          foreach ($contents->getContent() as $content) {
					$new_content .= $content->getContent() . "\n";
				}

				$header   = new Header(array(), $this->document);
				$contents = new PDFObject($this->document, $header, $new_content);
			}

			return $contents->getTextArray($this);
		}

		return array();
	}
}
