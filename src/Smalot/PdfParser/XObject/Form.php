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

namespace Smalot\PdfParser\XObject;

use Smalot\PdfParser\Header;
use Smalot\PdfParser\Page;
use Smalot\PdfParser\PDFObject;

/**
 * Class Form
 */
class Form extends Page
{
    public function getText(?Page $page = null): string
    {
        $header = new Header([], $this->document);
        $contents = new PDFObject($this->document, $header, $this->content, $this->config);

        return $contents->getText($this);
    }
    public function extractRawData(): array
    {
        /*
         * Now you can get the complete content of the object with the text on it
         */
        $extractedData = [];

        //This is the only difference to Page.php
        $header = new Header([], $this->document);
        $content = new PDFObject($this->document, $header, $this->content, $this->config);

        $values = $content->getContent();
        if (isset($values) && \is_array($values)) {
            $text = '';
            foreach ($values as $section) {
                $text .= $section->getContent();
            }
            $sectionsText = $this->getSectionsText($text);
            foreach ($sectionsText as $sectionText) {
                $commandsText = $this->getCommandsText($sectionText);
                foreach ($commandsText as $command) {
                    $extractedData[] = $command;
                }
            }
        } else {
            if ($this->isFpdf()) {
                $content = $this->getPDFObjectForFpdf();
            }
            $sectionsText = $content->getSectionsText($content->getContent());
            foreach ($sectionsText as $sectionText) {
                $commandsText = $content->getCommandsText($sectionText);
                foreach ($commandsText as $command) {
                    $extractedData[] = $command;
                }
            }
        }

        return $extractedData;
    }
    
    public function getTextArray(?Page $page = null): array
    {
        if ($this->isFpdf()) {
            $pdfObject = $this->getPDFObjectForFpdf();
            $newPdfObject = $this->createPDFObjectForFpdf();

            return $newPdfObject->getTextArray($pdfObject);
        } else {
            
            //This is the only difference to Page.php
            $header = new Header([], $this->document);
            if ($contents = new PDFObject($this->document, $header, $this->content, $this->config)) {
                if ($contents instanceof ElementMissing) {
                    return [];
                } elseif ($contents instanceof ElementNull) {
                    return [];
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

                        $header = new Header([], $this->document);
                        $contents = new PDFObject($this->document, $header, $new_content, $this->config);
                    } else {
                        try {
                            $contents->getTextArray($this);
                        } catch (\Throwable $e) {
                            return $contents->getTextArray();
                        }
                    }
                } elseif ($contents instanceof ElementArray) {
                    // Create a virtual global content.
                    $new_content = '';

                    /** @var PDFObject $content */
                    foreach ($contents->getContent() as $content) {
                        $new_content .= $content->getContent()."\n";
                    }

                    $header = new Header([], $this->document);
                    $contents = new PDFObject($this->document, $header, $new_content, $this->config);
                }

                return $contents->getTextArray($this);
            }

            return [];
        }
    }
}
