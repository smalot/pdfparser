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

use Smalot\PdfParser\Element\ElementMissing;

/**
 * Class Object
 *
 * @package Smalot\PdfParser
 */
class Object
{
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
     * @return Element|Object
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
     * @return array
     */
    public function getDetails()
    {
        $details = array();

        $details += $this->header->getValues();

        return $details;
    }

    /**
     * @return null|string
     */
    public function getContent()
    {
        return $this->content;
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
        $text_parts          = $this->getCommands($this->content);
        $current_font_size   = 0;
        $current_font        = new Font($this->document);
        $current_position_td = array('x' => false, 'y' => false);
        $current_position_tm = array('x' => false, 'y' => false);

        foreach ($text_parts as $commands) {

            // Skip non text bloc.
            if ($commands['type'] != 'BT') {
                continue;
            }

            foreach ($commands['command'] as $command) {

                switch ($command['operator']) {
                    // set character spacing
                    case 'Tc':
                        break;

                    // move text current point
                    case 'Td':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if ((floatval($x) <= 0 && floatval($y) > 0) ||
                            ($current_position_td['y'] !== false && floatval($y) != floatval($current_position_td['y']))
                        ) {
                            $text .= "\n";
                        }
                        $current_position_td = array('x' => $x, 'y' => $y);
                        break;

                    // move text current point and set leading
                    case 'TD':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if (floatval($y)) {
                            $text .= "\n\n";
                        } elseif (floatval($x) <= 0) {
                            $text .= ' ';
                        }
                        break;

                    case 'Tf':
                        list($id,)    = preg_split('/\s/s', $command['command']);
                        $id           = trim($id, '/');
                        $current_font = $page->getFont($id);
                        break;

                    case "'":
                    case 'Tj':
                        $command['command'] = array($command);
                    case 'TJ':
                        // Skip if not previously defined, should never happened.
                        if (is_null($current_font)) continue;

                        $sub_text = $current_font->decodeText($command['command']);
                        $text .= $sub_text;
                        break;

                    // set leading
                    case 'TL':
                        $text .= ' ';
                        break;

                    case 'Tm':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
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
                        //throw new \Exception('Operator not supported: ' . $command['operator']);
                }
            }
        }

        return $text;
    }

    /**
     * @return string
     */
//    public function __toString()
//    {
//        return (string)($this->getContent());
//    }

    /**
     * @param string $text_part
     * @param int    $offset
     *
     * @return array
     */
    public function getCommands($text_part, &$offset = 0)
    {
        $commands = $matches = array();

        while ($offset < strlen($text_part)) {
            // skip initial white space chars: \x00 null (NUL), \x09 horizontal tab (HT), \x0A line feed (LF), \x0C form feed (FF), \x0D carriage return (CR), \x20 space (SP)
            $offset += strspn($text_part, "\x00\x09\x0a\x0c\x0d\x20", $offset);
            $char   = $text_part[$offset];

            $operator  = '';
            $type      = '';
            $command   = false;

            switch ($char) {
                case '/':
                    $type = $char;
                    if (preg_match('/^\/[A-Z0-9\._\-\s]+?\s+([a-z]+)/si', substr($text_part, $offset), $matches)) {
                        $operator = $matches[1];
                        $command = trim(substr($matches[0], 0, strlen($operator) * -1));
                        $offset+= strlen($matches[0]);
                    }
                    break;

                case '[':
                case ']':
                    // array object
                    $type = $char;
                    if ($char == '[') {
                        ++$offset;
                        // get elements
                        $command = $this->getCommands($text_part, $offset);

                        if (preg_match('/^\s*[A-Z]{1,2}/si', substr($text_part, $offset), $matches)) {
                            $operator = trim($matches[0]);
                            $offset+= strlen($matches[0]);
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
                        $strpos = strpos($text_part, '>', $offset);
                        $command = substr($text_part, $offset, ($strpos - $offset));
                        $offset = $strpos + 1;
                    }

                    if (preg_match('/^\s*[A-Z]{1,2}/si', substr($text_part, $offset), $matches)) {
                        $command .= trim($matches[0]);
                        $offset+= strlen($matches[0]);
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
                                case '\\': { // REVERSE SOLIDUS (5Ch) (Backslash)
                                    // skip next character
                                    ++$strpos;
                                    break;
                                }
                                case '(': { // LEFT PARENHESIS (28h)
                                    ++$open_bracket;
                                    break;
                                }
                                case ')': { // RIGHT PARENTHESIS (29h)
                                    --$open_bracket;
                                    break;
                                }
                            }
                            ++$strpos;
                        }
                        $command = substr($text_part, $offset, ($strpos - $offset - 1));
                        $offset = $strpos;

                        if (preg_match('/^\s*[A-Z\']{1,2}/si', substr($text_part, $offset), $matches)) {
                            $operator = trim($matches[0]);
                            $offset += strlen($matches[0]);
                        }
                    }
                    break;

                default:

                    if (substr($text_part, $offset, 2) == 'T*') {
                        $operator = 'T*';
                        $command  = '';
                        $offset+= 2;
                    } elseif (preg_match('/^(?<data>([0-9\.\-]+\s*?)+)\s+(?<id>[A-Z]{1,3})/si', substr($text_part, $offset), $matches)) {
                        $operator = trim($matches['id']);
                        $command  = trim($matches['data']);
                        $offset   += strlen($matches[0]);
                    } elseif (preg_match('/^([0-9\.\-]+\s*?)+/si', substr($text_part, $offset), $matches)) {
                        $type    = 'numeric';
                        $command = trim($matches[0]);
                        $offset+= strlen($matches[0]);
                    } elseif (substr($text_part, $offset, 2) == 'BT' ||
                              substr($text_part, $offset, 2) == 'ET') {
                        if (substr($text_part, $offset, 2) == 'BT') {
                            $type = 'BT';
                            $offset += 2;
                            // get elements
                            $command = $this->getCommands($text_part, $offset);
                        } else {
                            $offset += 2;
                            break;
                        }
                    } elseif (preg_match('/^([A-Z\*]+)/si', substr($text_part, $offset), $matches)) {
                        $type     = '';
                        $operator = $matches[0];
                        $command  = '';
                        $offset   += strlen($matches[0]);
                    }
            }

            if ($command !== false) {
                $commands[] = array(
                    'operator' => $operator,
                    'type'     => $type,
                    'command'  => $command,
                );
            } else {
                break;
            }
        }

        return $commands;
    }

    /**
     * @param Document $document
     * @param string   $content
     *
     * @return Font|Image|Object|Page
     */
//    public static function parse(Document $document, $content = '')
//    {
//        $matches = array();
//

//        if (preg_match('/^\s*<</s', $content, $matches)) {
//            $position = 0;
//            $header   = Header::parse($content, $document, $position);
//            $content  = trim(substr($content, $position), " \n\r");
//        } else {
//            $header  = new Header(array(), $document);
//            $content = trim($content, " \n\r");
//        }
//
//        if (preg_match('/^\s*stream[\n\r]{0,2}(?<data>.*)endstream.*/s', $content, $matches)) {
//            $content = preg_replace('/[\n\r]{1,2}$/', '', $matches['data']);
//        }
//
//        if ($header->has('Filter')) {
//            if ($header->has('DecodeParms')) {
//                $decodeParms = $header->get('DecodeParms');
//                if ($decodeParms instanceof ElementArray) {
//                    $decodeParms = $decodeParms->getContent();
//                } else {
//                    $decodeParms = array($decodeParms);
//                }
//            } else {
//                $decodeParms = array();
//            }
//
//            $filters = (array)($header->get('Filter')->getContent());
//
//            foreach ($filters as $position => $filter) {
//                try {
//                    if (isset($decodeParms[$position])) {
//                        $content = Filters::decodeFilter((string)$filter, $content, $decodeParms[$position]);
//                    } else {
//                        $content = Filters::decodeFilter((string)$filter, $content);
//                    }
//                } catch (\Exception $e) {
////                    echo 'error: ' . $e->getMessage() . "\n";
//                    trigger_error($e->getMessage());
//                    $content = '';
//                    break;
//                }
//            }
//        }
//
//        return self::factory($document, $header, $content);
//    }

    public static function factory($document, $header, $content)
    {
        switch ($header->get('Type')->getContent()) {
            case 'XObject':
                switch ($header->get('Subtype')->getContent()) {
                    case 'Image':
                        return new Image($document, $header, $content);

                    default:
                        return new Object($document, $header, $content);
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

                return new $classname($document, $header, $content);

            default:
                return new Object($document, $header, $content);
        }
    }
}
