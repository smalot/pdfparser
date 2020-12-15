<?php

/**
 * @file
 *          This file is part of the PdfParser library.
 *
 * @author  Sébastien MALOT <sebastien@malot.fr>
 * @date    2017-01-03
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

namespace Smalot\PdfParser;

use Smalot\PdfParser\Element\ElementArray;
use Smalot\PdfParser\Element\ElementMissing;
use Smalot\PdfParser\Element\ElementNull;
use Smalot\PdfParser\Element\ElementXRef;

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
     * @var array
     */
    protected $dataTm = null;

    /**
     * @return Font[]
     */
    public function getFonts()
    {
        if (null !== $this->fonts) {
            return $this->fonts;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('Font')) {
            if ($resources->get('Font') instanceof ElementMissing) {
                return [];
            }

            if ($resources->get('Font') instanceof Header) {
                $fonts = $resources->get('Font')->getElements();
            } else {
                $fonts = $resources->get('Font')->getHeader()->getElements();
            }

            $table = [];

            foreach ($fonts as $id => $font) {
                if ($font instanceof Font) {
                    $table[$id] = $font;

                    // Store too on cleaned id value (only numeric)
                    $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                    if ('' != $id) {
                        $table[$id] = $font;
                    }
                }
            }

            return $this->fonts = $table;
        }

        return [];
    }

    /**
     * @param string $id
     *
     * @return Font|null
     */
    public function getFont($id)
    {
        $fonts = $this->getFonts();

        if (isset($fonts[$id])) {
            return $fonts[$id];
        }

        // According to the PDF specs (https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDF32000_2008.pdf, page 238)
        // "The font resource name presented to the Tf operator is arbitrary, as are the names for all kinds of resources"
        // Instead, we search for the unfiltered name first and then do this cleaning as a fallback, so all tests still pass.

        if (isset($fonts[$id])) {
            return $fonts[$id];
        } else {
            $id = preg_replace('/[^0-9\.\-_]/', '', $id);
            if (isset($fonts[$id])) {
                return $fonts[$id];
            }
        }

        return null;
    }

    /**
     * Support for XObject
     *
     * @return PDFObject[]
     */
    public function getXObjects()
    {
        if (null !== $this->xobjects) {
            return $this->xobjects;
        }

        $resources = $this->get('Resources');

        if (method_exists($resources, 'has') && $resources->has('XObject')) {
            if ($resources->get('XObject') instanceof Header) {
                $xobjects = $resources->get('XObject')->getElements();
            } else {
                $xobjects = $resources->get('XObject')->getHeader()->getElements();
            }

            $table = [];

            foreach ($xobjects as $id => $xobject) {
                $table[$id] = $xobject;

                // Store too on cleaned id value (only numeric)
                $id = preg_replace('/[^0-9\.\-_]/', '', $id);
                if ('' != $id) {
                    $table[$id] = $xobject;
                }
            }

            return $this->xobjects = $table;
        }

        return [];
    }

    /**
     * @param string $id
     *
     * @return PDFObject|null
     */
    public function getXObject($id)
    {
        $xobjects = $this->getXObjects();

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        }

        return null;
        /*$id = preg_replace('/[^0-9\.\-_]/', '', $id);

        if (isset($xobjects[$id])) {
            return $xobjects[$id];
        } else {
            return null;
        }*/
    }

    /**
     * @param Page $page
     *
     * @return string
     */
    public function getText(self $page = null)
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

                    $header = new Header([], $this->document);
                    $contents = new PDFObject($this->document, $header, $new_content);
                }
            } elseif ($contents instanceof ElementArray) {
                // Create a virtual global content.
                $new_content = '';

                foreach ($contents->getContent() as $content) {
                    $new_content .= $content->getContent()."\n";
                }

                $header = new Header([], $this->document);
                $contents = new PDFObject($this->document, $header, $new_content);
            }

            return $contents->getText($this);
        }

        return '';
    }

    /**
     * @param Page $page
     *
     * @return array
     */
    public function getTextArray(self $page = null)
    {
        if ($contents = $this->get('Contents')) {
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
                    $contents = new PDFObject($this->document, $header, $new_content);
                }
            } elseif ($contents instanceof ElementArray) {
                // Create a virtual global content.
                $new_content = '';

                /** @var PDFObject $content */
                foreach ($contents->getContent() as $content) {
                    $new_content .= $content->getContent()."\n";
                }

                $header = new Header([], $this->document);
                $contents = new PDFObject($this->document, $header, $new_content);
            }

            return $contents->getTextArray($this);
        }

        return [];
    }

    /**
     * Gets all the text data with its internal representation of the page.
     *
     * @return array An array with the data and the internal representation
     */
    public function extractRawData()
    {
        /*
         * Now you can get the complete content of the object with the text on it
         */
        $extractedData = [];
        $content = $this->get('Contents');
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
            $sectionsText = $content->getSectionsText($content->getContent());
            foreach ($sectionsText as $sectionText) {
                $extractedData[] = ['t' => '', 'o' => 'BT', 'c' => ''];

                $commandsText = $content->getCommandsText($sectionText);
                foreach ($commandsText as $command) {
                    $extractedData[] = $command;
                }
            }
        }

        return $extractedData;
    }

    /**
     * Gets all the decoded text data with it internal representation from a page.
     *
     * @param array $extractedRawData the extracted data return by extractRawData or
     *                                null if extractRawData should be called
     *
     * @return array An array with the data and the internal representation
     */
    public function extractDecodedRawData($extractedRawData = null)
    {
        if (!isset($extractedRawData) || !$extractedRawData) {
            $extractedRawData = $this->extractRawData();
        }
        $currentFont = null;
        foreach ($extractedRawData as &$command) {
            if ('Tj' == $command['o'] || 'TJ' == $command['o']) {
                $data = $command['c'];
                if (!\is_array($data)) {
                    $tmpText = '';
                    if (isset($currentFont)) {
                        $tmpText = $currentFont->decodeOctal($data);
                        //$tmpText = $currentFont->decodeHexadecimal($tmpText, false);
                    }
                    $tmpText = str_replace(
                            ['\\\\', '\(', '\)', '\n', '\r', '\t', '\ '],
                            ['\\', '(', ')', "\n", "\r", "\t", ' '],
                            $tmpText
                    );
                    $tmpText = utf8_encode($tmpText);
                    if (isset($currentFont)) {
                        $tmpText = $currentFont->decodeContent($tmpText);
                    }
                    $command['c'] = $tmpText;
                    continue;
                }
                $numText = \count($data);
                for ($i = 0; $i < $numText; ++$i) {
                    if (0 != ($i % 2)) {
                        continue;
                    }
                    $tmpText = $data[$i]['c'];
                    $decodedText = '';
                    if (isset($currentFont)) {
                        $decodedText = $currentFont->decodeOctal($tmpText);
                        //$tmpText = $currentFont->decodeHexadecimal($tmpText, false);
                    }
                    $decodedText = str_replace(
                            ['\\\\', '\(', '\)', '\n', '\r', '\t', '\ '],
                            ['\\', '(', ')', "\n", "\r", "\t", ' '],
                            $decodedText
                    );
                    $decodedText = utf8_encode($decodedText);
                    if (isset($currentFont)) {
                        $decodedText = $currentFont->decodeContent($decodedText);
                    }
                    $command['c'][$i]['c'] = $decodedText;
                    continue;
                }
            } elseif ('Tf' == $command['o'] || 'TF' == $command['o']) {
                $fontId = explode(' ', $command['c'])[0];
                $currentFont = $this->getFont($fontId);
                continue;
            }
        }

        return $extractedRawData;
    }

    /**
     * Gets just the Text commands that are involved in text positions and
     * Text Matrix (Tm)
     *
     * It extract just the PDF commands that are involved with text positions, and
     * the Text Matrix (Tm). These are: BT, ET, TL, Td, TD, Tm, T*, Tj, ', ", and TJ
     *
     * @param array $extractedDecodedRawData The data extracted by extractDecodeRawData.
     *                                       If it is null, the method extractDecodeRawData is called.
     *
     * @return array An array with the text command of the page
     */
    public function getDataCommands($extractedDecodedRawData = null)
    {
        if (!isset($extractedDecodedRawData) || !$extractedDecodedRawData) {
            $extractedDecodedRawData = $this->extractDecodedRawData();
        }
        $extractedData = [];
        foreach ($extractedDecodedRawData as $command) {
            switch ($command['o']) {
                /*
                 * BT
                 * Begin a text object, inicializind the Tm and Tlm to identity matrix
                 */
                case 'BT':
                    $extractedData[] = $command;
                    break;

                /*
                 * ET
                 * End a text object, discarding the text matrix
                 */
                case 'ET':
                    $extractedData[] = $command;
                    break;

                /*
                 * leading TL
                 * Set the text leading, Tl, to leading. Tl is used by the T*, ' and " operators.
                 * Initial value: 0
                 */
                case 'TL':
                    $extractedData[] = $command;
                    break;

                /*
                 * tx ty Td
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty.
                 */
                case 'Td':
                    $extractedData[] = $command;
                    break;

                /*
                 * tx ty TD
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty. As a side effect, this operator set the leading
                 * parameter in the text state. This operator has the same effect as the
                 * code:
                 * -ty TL
                 * tx ty Td
                 */
                case 'TD':
                    $extractedData[] = $command;
                    break;

                /*
                 * a b c d e f Tm
                 * Set the text matrix, Tm, and the text line matrix, Tlm. The operands are
                 * all numbers, and the initial value for Tm and Tlm is the identity matrix
                 * [1 0 0 1 0 0]
                 */
                case 'Tm':
                    $extractedData[] = $command;
                    break;

                /*
                 * T*
                 * Move to the start of the next line. This operator has the same effect
                 * as the code:
                 * 0 Tl Td
                 * Where Tl is the current leading parameter in the text state.
                 */
                case 'T*':
                    $extractedData[] = $command;
                    break;

                /*
                 * string Tj
                 * Show a Text String
                 */
                case 'Tj':
                    $extractedData[] = $command;
                    break;

                /*
                 * string '
                 * Move to the next line and show a text string. This operator has the
                 * same effect as the code:
                 * T*
                 * string Tj
                 */
                case "'":
                    $extractedData[] = $command;
                    break;

                /*
                 * aw ac string "
                 * Move to the next lkine and show a text string, using aw as the word
                 * spacing and ac as the character spacing. This operator has the same
                 * effect as the code:
                 * aw Tw
                 * ac Tc
                 * string '
                 * Tw set the word spacing, Tw, to wordSpace.
                 * Tc Set the character spacing, Tc, to charsSpace.
                 */
                case '"':
                    $extractedData[] = $command;
                    break;

                /*
                 * array TJ
                 * Show one or more text strings allow individual glyph positioning.
                 * Each lement of array con be a string or a number. If the element is
                 * a string, this operator shows the string. If it is a number, the
                 * operator adjust the text position by that amount; that is, it translates
                 * the text matrix, Tm. This amount is substracted form the current
                 * horizontal or vertical coordinate, depending on the writing mode.
                 * in the default coordinate system, a positive adjustment has the effect
                 * of moving the next glyph painted either to the left or down by the given
                 * amount.
                 */
                case 'TJ':
                    $extractedData[] = $command;
                    break;
                default:
            }
        }

        return $extractedData;
    }

    /**
     * Gets the Text Matrix of the text in the page
     *
     * Return an array where every item is an array where the first item is the
     * Text Matrix (Tm) and the second is a string with the text data.  The Text matrix
     * is an array of 6 numbers. The last 2 numbers are the coordinates X and Y of the
     * text. The first 4 numbers has to be with Scalation, Rotation and Skew of the text.
     *
     * @param array $dataCommands the data extracted by getDataCommands
     *                            if null getDataCommands is called
     *
     * @return array an array with the data of the page including the Tm information
     *               of any text in the page
     */
    public function getDataTm($dataCommands = null)
    {
        if (!isset($dataCommands) || !$dataCommands) {
            $dataCommands = $this->getDataCommands();
        }

        /*
         * At the beginning of a text object Tm is the identity matrix
         */
        $defaultTm = ['1', '0', '0', '1', '0', '0'];

        /*
         *  Set the text leading used by T*, ' and " operators
         */
        $defaultTl = 0;

        /*
         * Setting where are the X and Y coordinates in the matrix (Tm)
         */
        $x = 4;
        $y = 5;
        $Tx = 0;
        $Ty = 0;

        $Tm = $defaultTm;
        $Tl = $defaultTl;

        $extractedTexts = $this->getTextArray();
        $extractedData = [];
        foreach ($dataCommands as $command) {
            $currentText = $extractedTexts[\count($extractedData)];
            switch ($command['o']) {
                /*
                 * BT
                 * Begin a text object, inicializind the Tm and Tlm to identity matrix
                 */
                case 'BT':
                    $Tm = $defaultTm;
                    $Tl = $defaultTl; //review this.
                    $Tx = 0;
                    $Ty = 0;
                    break;

                /*
                 * ET
                 * End a text object, discarding the text matrix
                 */
                case 'ET':
                    $Tm = $defaultTm;
                    $Tl = $defaultTl;  //review this
                    $Tx = 0;
                    $Ty = 0;
                    break;

                /*
                 * leading TL
                 * Set the text leading, Tl, to leading. Tl is used by the T*, ' and " operators.
                 * Initial value: 0
                 */
                case 'TL':
                    $Tl = (float) $command['c'];
                    break;

                /*
                 * tx ty Td
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty.
                 */
                case 'Td':
                    $coord = explode(' ', $command['c']);
                    $Tx += (float) $coord[0];
                    $Ty += (float) $coord[1];
                    $Tm[$x] = (string) $Tx;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * tx ty TD
                 * Move to the start of the next line, offset form the start of the
                 * current line by tx, ty. As a side effect, this operator set the leading
                 * parameter in the text state. This operator has the same effect as the
                 * code:
                 * -ty TL
                 * tx ty Td
                 */
                case 'TD':
                    $coord = explode(' ', $command['c']);
                    $Tl = (float) $coord[1];
                    $Tx += (float) $coord[0];
                    $Ty -= (float) $coord[1];
                    $Tm[$x] = (string) $Tx;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * a b c d e f Tm
                 * Set the text matrix, Tm, and the text line matrix, Tlm. The operands are
                 * all numbers, and the initial value for Tm and Tlm is the identity matrix
                 * [1 0 0 1 0 0]
                 */
                case 'Tm':
                    $Tm = explode(' ', $command['c']);
                    $Tx = (float) $Tm[$x];
                    $Ty = (float) $Tm[$y];
                    break;

                /*
                 * T*
                 * Move to the start of the next line. This operator has the same effect
                 * as the code:
                 * 0 Tl Td
                 * Where Tl is the current leading parameter in the text state.
                 */
                case 'T*':
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    break;

                /*
                 * string Tj
                 * Show a Text String
                 */
                case 'Tj':
                    $extractedData[] = [$Tm, $currentText];
                    break;

                /*
                 * string '
                 * Move to the next line and show a text string. This operator has the
                 * same effect as the code:
                 * T*
                 * string Tj
                 */
                case "'":
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    $extractedData[] = [$Tm, $currentText];
                    break;

                /*
                 * aw ac string "
                 * Move to the next line and show a text string, using aw as the word
                 * spacing and ac as the character spacing. This operator has the same
                 * effect as the code:
                 * aw Tw
                 * ac Tc
                 * string '
                 * Tw set the word spacing, Tw, to wordSpace.
                 * Tc Set the character spacing, Tc, to charsSpace.
                 */
                case '"':
                    $data = explode(' ', $currentText);
                    $Ty -= $Tl;
                    $Tm[$y] = (string) $Ty;
                    $extractedData[] = [$Tm, $data[2]]; //Verify
                    break;

                /*
                 * array TJ
                 * Show one or more text strings allow individual glyph positioning.
                 * Each lement of array con be a string or a number. If the element is
                 * a string, this operator shows the string. If it is a number, the
                 * operator adjust the text position by that amount; that is, it translates
                 * the text matrix, Tm. This amount is substracted form the current
                 * horizontal or vertical coordinate, depending on the writing mode.
                 * in the default coordinate system, a positive adjustment has the effect
                 * of moving the next glyph painted either to the left or down by the given
                 * amount.
                 */
                case 'TJ':
                    $extractedData[] = [$Tm, $currentText];
                    break;
                default:
            }
        }
        $this->dataTm = $extractedData;

        return $extractedData;
    }

    /**
     * Gets text data that are around the given coordinates (X,Y)
     *
     * If the text is in near the given coordinates (X,Y) (or the TM info),
     * the text is returned.  The extractedData return by getDataTm, could be use to see
     * where is the coordinates of a given text, using the TM info for it.
     *
     * @param float $x      The X value of the coordinate to search for. if null
     *                      just the Y value is considered (same Row)
     * @param float $y      The Y value of the coordinate to search for
     *                      just the X value is considered (same column)
     * @param float $xError The value less or more to consider an X to be "near"
     * @param float $yError The value less or more to consider an Y to be "near"
     *
     * @return array An array of text that are near the given coordinates. If no text
     *               "near" the x,y coordinate, an empty array is returned. If Both, x
     *               and y coordinates are null, null is returned.
     */
    public function getTextXY($x = null, $y = null, $xError = 0, $yError = 0)
    {
        if (!isset($this->dataTm) || !$this->dataTm) {
            $this->getDataTm();
        }

        if (null !== $x) {
            $x = (float) $x;
        }

        if (null !== $y) {
            $y = (float) $y;
        }

        if (null === $x && null === $y) {
            return [];
        }

        $xError = (float) $xError;
        $yError = (float) $yError;

        $extractedData = [];
        foreach ($this->dataTm as $item) {
            $tm = $item[0];
            $xTm = (float) $tm[4];
            $yTm = (float) $tm[5];
            $text = $item[1];
            if (null === $y) {
                if (($xTm >= ($x - $xError)) &&
                    ($xTm <= ($x + $xError))) {
                    $extractedData[] = [$tm, $text];
                    continue;
                }
            }
            if (null === $x) {
                if (($yTm >= ($y - $yError)) &&
                    ($yTm <= ($y + $yError))) {
                    $extractedData[] = [$tm, $text];
                    continue;
                }
            }
            if (($xTm >= ($x - $xError)) &&
                ($xTm <= ($x + $xError)) &&
                ($yTm >= ($y - $yError)) &&
                ($yTm <= ($y + $yError))) {
                $extractedData[] = [$tm, $text];
                continue;
            }
        }

        return $extractedData;
    }
}
