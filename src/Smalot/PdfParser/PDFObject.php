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

use Smalot\PdfParser\XObject\Form;
use Smalot\PdfParser\XObject\Image;

/**
 * Class PDFObject
 *
 * @package Smalot\PdfParser
 */
class PDFObject
{
    const TYPE = 't';

    const OPERATOR = 'o';

    const COMMAND = 'c';

    /**
     * The recursion stack.
     *
     * @var array
     */
    static $recursionStack = array();

    /**
     * @var Document
     */
    protected $document = null;

    /**
     * @var Header
     */
    protected $header = null;

    /**
     * @var string
     */
    protected $content = null;

    /**
     * @param Document $document
     * @param Header   $header
     * @param string   $content
     */
    public function __construct(Document $document, Header $header = null, $content = null)
    {
        $this->document = $document;
        $this->header   = !is_null($header) ? $header : new Header();
        $this->content  = $content;
    }

    /**
     *
     */
    public function init()
    {

    }

    /**
     * @return null|Header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $name
     *
     * @return Element|PDFObject
     */
    public function get($name)
    {
        return $this->header->get($name);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->header->has($name);
    }

    /**
     * @param bool $deep
     *
     * @return array
     */
    public function getDetails($deep = true)
    {
        return $this->header->getDetails($deep);
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     */
    public function cleanContent($content, $char = 'X')
    {
        $char    = $char[0];
        $content = str_replace(array('\\\\', '\\)', '\\('), $char . $char, $content);

        // Remove image bloc with binary content
        preg_match_all('/\s(BI\s.*?(\sID\s).*?(\sEI))\s/s', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $part) {
            $content = substr_replace($content, str_repeat($char, strlen($part[0])), $part[1], strlen($part[0]));
        }

        // Clean content in square brackets [.....]
        preg_match_all('/\[((\(.*?\)|[0-9\.\-\s]*)*)\]/s', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, strlen($part[0])), $part[1], strlen($part[0]));
        }

        // Clean content in round brackets (.....)
        preg_match_all('/\((.*?)\)/s', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, strlen($part[0])), $part[1], strlen($part[0]));
        }

        // Clean structure
        if ($parts = preg_split('/(<|>)/s', $content, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE)) {
            $content = '';
            $level   = 0;
            foreach ($parts as $part) {
                if ($part == '<') {
                    $level++;
                }

                $content .= ($level == 0 ? $part : str_repeat($char, strlen($part)));

                if ($part == '>') {
                    $level--;
                }
            }
        }

        // Clean BDC and EMC markup
        preg_match_all(
            '/(\/[A-Za-z0-9\_]*\s*' . preg_quote($char) . '*BDC)/s',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, strlen($part[0])), $part[1], strlen($part[0]));
        }

        preg_match_all('/\s(EMC)\s/s', $content, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $part) {
            $content = substr_replace($content, str_repeat($char, strlen($part[0])), $part[1], strlen($part[0]));
        }

        return $content;
    }

    /**
     * @param $content
     *
     * @return array
     */
    public function getSectionsText($content)
    {
        $sections    = array();
        $content     = ' ' . $content . ' ';
        $textCleaned = $this->cleanContent($content, '_');

        // Extract text blocks.
        if (preg_match_all('/\s+BT[\s|\(|\[]+(.*?)\s*ET/s', $textCleaned, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $part) {
                $text    = $part[0];
                if ($text === '') {
                    continue;
                }
                $offset  = $part[1];
                $section = substr($content, $offset, strlen($text));

                // Removes BDC and EMC markup.
                $section = preg_replace('/(\/[A-Za-z0-9]+\s*<<.*?)(>>\s*BDC)(.*?)(EMC\s+)/s', '${3}', $section . ' ');

                $sections[] = $section;
            }
        }

        // Extract 'do' commands.
        if (preg_match_all('/(\/[A-Za-z0-9\.\-_]+\s+Do)\s/s', $textCleaned, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $part) {
                $text    = $part[0];
                $offset  = $part[1];
                $section = substr($content, $offset, strlen($text));

                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * @param Page
     *
     * @return string
     * @throws \Exception
     */
    public function getText(Page $page = null)
    {
        $text                = '';
        $sections            = $this->getSectionsText($this->content);
        $current_font = null;

        foreach ($this->document->getObjects() as $obj) {
            if ($obj instanceof Font) {
                $current_font = $obj;
                break;
            }
        }

        if ($current_font === null) {
            $current_font = new Font($this->document);
        }

        $current_position_td = array('x' => false, 'y' => false);
        $current_position_tm = array('x' => false, 'y' => false);

        array_push(self::$recursionStack, $this->getUniqueId());

        foreach ($sections as $section) {

            $commands = $this->getCommandsText($section);

            foreach ($commands as $command) {

                switch ($command[self::OPERATOR]) {
                    // set character spacing
                    case 'Tc':
                        break;

                    // move text current point
                    case 'Td':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if ((floatval($x) <= 0) ||
                            ($current_position_td['y'] !== false && floatval($y) < floatval($current_position_td['y']))
                        ) {
                            // vertical offset
                            $text .= "\n";
                        } elseif ($current_position_td['x'] !== false && floatval($x) > floatval(
                                $current_position_td['x']
                            )
                        ) {
                            // horizontal offset
                            $text .= ' ';
                        }
                        $current_position_td = array('x' => $x, 'y' => $y);
                        break;

                    // move text current point and set leading
                    case 'TD':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if (floatval($y) < 0) {
                            $text .= "\n";
                        } elseif (floatval($x) <= 0) {
                            $text .= ' ';
                        }
                        break;

                    case 'Tf':
                        list($id,) = preg_split('/\s/s', $command[self::COMMAND]);
                        $id           = trim($id, '/');
                        if (!is_null($page)) {
                            $current_font = $page->getFont($id);
                        }
                        break;

                    case "'":
                    case 'Tj':
                        $command[self::COMMAND] = array($command);
                    case 'TJ':
                        // Skip if not previously defined, should never happened.
                        if (is_null($current_font)) {
                            // Fallback
                            // TODO : Improve
                            $text .= $command[self::COMMAND][0][self::COMMAND];
                            continue;
                        }

                        $sub_text = $current_font->decodeText($command[self::COMMAND]);
                        $text .= $sub_text;
                        break;

                    // set leading
                    case 'TL':
                        $text .= ' ';
                        break;

                    case 'Tm':
                        $args = preg_split('/\s/s', $command[self::COMMAND]);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if ($current_position_tm['x'] !== false) {
                            $delta = abs(floatval($x) - floatval($current_position_tm['x']));
                            if ($delta > 10) {
                                $text .= "\t";
                            }
                        }
                        if ($current_position_tm['y'] !== false) {
                            $delta = abs(floatval($y) - floatval($current_position_tm['y']));
                            if ($delta > 10) {
                                $text .= "\n";
                            }
                        }
                        $current_position_tm = array('x' => $x, 'y' => $y);
                        break;

                    // set super/subscripting text rise
                    case 'Ts':
                        break;

                    // set word spacing
                    case 'Tw':
                        break;

                    // set horizontal scaling
                    case 'Tz':
                        $text .= "\n";
                        break;

                    // move to start of next line
                    case 'T*':
                        $text .= "\n";
                        break;

                    case 'Da':
                        break;

                    case 'Do':
                        if (!is_null($page)) {
                            $args    = preg_split('/\s/s', $command[self::COMMAND]);
                            $id      = trim(array_pop($args), '/ ');
                            $xobject = $page->getXObject($id);


                             // @todo $xobject could be a ElementXRef object, which would then throw an error
                             if ( is_object($xobject) && $xobject instanceof PDFObject && !in_array($xobject->getUniqueId(), self::$recursionStack) ) {
                                // Not a circular reference.
                                $text .= $xobject->getText($page);
                            }
                        }
                        break;

                    case 'rg':
                    case 'RG':
                        break;

                    case 're':
                        break;

                    case 'co':
                        break;

                    case 'cs':
                        break;

                    case 'gs':
                        break;

                    case 'en':
                        break;

                    case 'sc':
                    case 'SC':
                        break;

                    case 'g':
                    case 'G':
                        break;

                    case 'V':
                        break;

                    case 'vo':
                    case 'Vo':
                        break;

                    default:
                }
            }
        }

        array_pop(self::$recursionStack);

        return $text . ' ';
    }

	/**
	 * @param Page
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getTextArray(Page $page = null)
	{
		$text                = array();
		$sections            = $this->getSectionsText($this->content);
		$current_font        = new Font($this->document);

		foreach ($sections as $section) {

			$commands = $this->getCommandsText($section);

			foreach ($commands as $command) {

				switch ($command[self::OPERATOR]) {
					// set character spacing
					case 'Tc':
						break;

					// move text current point
					case 'Td':
						break;

					// move text current point and set leading
					case 'TD':
						break;

					case 'Tf':
						list($id,) = preg_split('/\s/s', $command[self::COMMAND]);
						$id           = trim($id, '/');
						$current_font = $page->getFont($id);
						break;

					case "'":
					case 'Tj':
						$command[self::COMMAND] = array($command);
					case 'TJ':
						// Skip if not previously defined, should never happened.
						if (is_null($current_font)) {
							// Fallback
							// TODO : Improve
							$text[] = $command[self::COMMAND][0][self::COMMAND];
							continue;
						}

						$sub_text = $current_font->decodeText($command[self::COMMAND]);
						$text[] = $sub_text;
						break;

					// set leading
					case 'TL':
						break;

					case 'Tm':
						break;

					// set super/subscripting text rise
					case 'Ts':
						break;

					// set word spacing
					case 'Tw':
						break;

					// set horizontal scaling
					case 'Tz':
						//$text .= "\n";
						break;

					// move to start of next line
					case 'T*':
						//$text .= "\n";
						break;

					case 'Da':
						break;

					case 'Do':
						if (!is_null($page)) {
							$args = preg_split('/\s/s', $command[self::COMMAND]);
							$id   = trim(array_pop($args), '/ ');
							if ($xobject = $page->getXObject($id)) {
								$text[] = $xobject->getText($page);
							}
						}
						break;

					case 'rg':
					case 'RG':
						break;

					case 're':
						break;

					case 'co':
						break;

					case 'cs':
						break;

					case 'gs':
						break;

					case 'en':
						break;

					case 'sc':
					case 'SC':
						break;

					case 'g':
					case 'G':
						break;

					case 'V':
						break;

					case 'vo':
					case 'Vo':
						break;

					default:
				}
			}
		}

		return $text;
	}


    /**
     * @param string $text_part
     * @param int    $offset
     *
     * @return array
     */
    public function getCommandsText($text_part, &$offset = 0)
    {
        $commands = $matches = array();

        while ($offset < strlen($text_part)) {
            $offset += strspn($text_part, "\x00\x09\x0a\x0c\x0d\x20", $offset);
            $char = $text_part[$offset];

            $operator = '';
            $type     = '';
            $command  = false;

            switch ($char) {
                case '/':
                    $type = $char;
                    if (preg_match(
                        '/^\/([A-Z0-9\._,\+]+\s+[0-9.\-]+)\s+([A-Z]+)\s*/si',
                        substr($text_part, $offset),
                        $matches
                    )
                    ) {
                        $operator = $matches[2];
                        $command  = $matches[1];
                        $offset += strlen($matches[0]);
                    } elseif (preg_match(
                        '/^\/([A-Z0-9\._,\+]+)\s+([A-Z]+)\s*/si',
                        substr($text_part, $offset),
                        $matches
                    )
                    ) {
                        $operator = $matches[2];
                        $command  = $matches[1];
                        $offset += strlen($matches[0]);
                    }
                    break;

                case '[':
                case ']':
                    // array object
                    $type = $char;
                    if ($char == '[') {
                        ++$offset;
                        // get elements
                        $command = $this->getCommandsText($text_part, $offset);

                        if (preg_match('/^\s*[A-Z]{1,2}\s*/si', substr($text_part, $offset), $matches)) {
                            $operator = trim($matches[0]);
                            $offset += strlen($matches[0]);
                        }
                    } else {
                        ++$offset;
                        break;
                    }
                    break;

                case '<':
                case '>':
                    // array object
                    $type = $char;
                    ++$offset;
                    if ($char == '<') {
                        $strpos  = strpos($text_part, '>', $offset);
                        $command = substr($text_part, $offset, ($strpos - $offset));
                        $offset  = $strpos + 1;
                    }

                    if (preg_match('/^\s*[A-Z]{1,2}\s*/si', substr($text_part, $offset), $matches)) {
                        $operator = trim($matches[0]);
                        $offset += strlen($matches[0]);
                    }
                    break;

                case '(':
                case ')':
                    ++$offset;
                    $type   = $char;
                    $strpos = $offset;
                    if ($char == '(') {
                        $open_bracket = 1;
                        while ($open_bracket > 0) {
                            if (!isset($text_part[$strpos])) {
                                break;
                            }
                            $ch = $text_part[$strpos];
                            switch ($ch) {
                                case '\\':
                                { // REVERSE SOLIDUS (5Ch) (Backslash)
                                    // skip next character
                                    ++$strpos;
                                    break;
                                }
                                case '(':
                                { // LEFT PARENHESIS (28h)
                                    ++$open_bracket;
                                    break;
                                }
                                case ')':
                                { // RIGHT PARENTHESIS (29h)
                                    --$open_bracket;
                                    break;
                                }
                            }
                            ++$strpos;
                        }
                        $command = substr($text_part, $offset, ($strpos - $offset - 1));
                        $offset  = $strpos;

                        if (preg_match('/^\s*([A-Z\']{1,2})\s*/si', substr($text_part, $offset), $matches)) {
                            $operator = $matches[1];
                            $offset += strlen($matches[0]);
                        }
                    }
                    break;

                default:

                    if (substr($text_part, $offset, 2) == 'ET') {
                        break;
                    } elseif (preg_match(
                        '/^\s*(?P<data>([0-9\.\-]+\s*?)+)\s+(?P<id>[A-Z]{1,3})\s*/si',
                        substr($text_part, $offset),
                        $matches
                    )
                    ) {
                        $operator = trim($matches['id']);
                        $command  = trim($matches['data']);
                        $offset += strlen($matches[0]);
                    } elseif (preg_match('/^\s*([0-9\.\-]+\s*?)+\s*/si', substr($text_part, $offset), $matches)) {
                        $type    = 'n';
                        $command = trim($matches[0]);
                        $offset += strlen($matches[0]);
                    } elseif (preg_match('/^\s*([A-Z\*]+)\s*/si', substr($text_part, $offset), $matches)) {
                        $type     = '';
                        $operator = $matches[1];
                        $command  = '';
                        $offset += strlen($matches[0]);
                    }
            }

            if ($command !== false) {
                $commands[] = array(
                    self::TYPE     => $type,
                    self::OPERATOR => $operator,
                    self::COMMAND  => $command,
                );
            } else {
                break;
            }
        }

        return $commands;
    }

    /**
     * @param $document Document
     * @param $header   Header
     * @param $content  string
     *
     * @return PDFObject
     */
    public static function factory(Document $document, Header $header, $content)
    {
        switch ($header->get('Type')->getContent()) {
            case 'XObject':
                switch ($header->get('Subtype')->getContent()) {
                    case 'Image':
                        return new Image($document, $header, $content);

                    case 'Form':
                        return new Form($document, $header, $content);

                    default:
                        return new PDFObject($document, $header, $content);
                }
                break;

            case 'Pages':
                return new Pages($document, $header, $content);

            case 'Page':
                return new Page($document, $header, $content);

            case 'Encoding':
                return new Encoding($document, $header, $content);

            case 'Font':
                $subtype   = $header->get('Subtype')->getContent();
                $classname = '\Smalot\PdfParser\Font\Font' . $subtype;

                if (class_exists($classname)) {
                    return new $classname($document, $header, $content);
                } else {
                    return new Font($document, $header, $content);
                }

            default:
                return new PDFObject($document, $header, $content);
        }
    }

    /**
     * Returns unique id identifying the object.
     *
     * @return string
     */
    protected function getUniqueId()
    {
        return spl_object_hash($this);
    }
}
