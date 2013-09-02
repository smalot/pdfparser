<?php

/**
 * @file
 * This file is part of the PdfParser library.
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
        $this->header   = !is_null($header)?$header:new Header();
        $this->content  = $content;
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
        $text              = '';
        $text_parts        = $this->getTextParts();
        $current_font      = null;
        $current_font_size = 0;
        $current_position  = array('x' => false, 'y' => false);

        foreach ($text_parts as $text_part) {
            $commands = $this->getCommandsFromTextPart($text_part);

            foreach ($commands as $command) {
//                echo 'command: ' . $command['operator'] . ': ' . $command['command'] . "\n";

                switch ($command['operator']) {
                    // set character spacing
                    case 'Tc':
                        break;

                    // move text current point
                    case 'Td':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if (floatval($x) <= 0 && floatval($y) > 0) {
                            $text .= "\n";
                        }
//                        $text .= '(move Td:'.$x.' x '.$y.')';
                        break;

                    // move text current point and set leading
                    case 'TD':
                        break;

                    case 'Tf':
                        list($id,) = preg_split('/\s/s', $command['command']);
                        //var_dump('fontsize:' . $command['command']);
                        $current_font = $page->getFont($id);
                        break;

                    case 'TJ':
                        $tmp = trim($command['command'], '[]');
                        if ($tmp[0] == '<') {
                            $text .= $this->decodeHexadecimal($tmp, $current_font);
                        } else {
                            $text .= $this->decodeText($tmp, $current_font);
                        }
                        break;

                    case 'Tj':
//                        echo 'avant: "' . $command['command'] . "\"\n";
                        //$tmp = substr($command['command'], 1, -1);
                        if ($command['command'][0] == '<') {
                            $command['command'] = '(' . $command['command'] . ')';
                        }
                        $tmp = $this->decodeText($command['command'], $current_font);
//                        echo 'avant: "' . $tmp . "\"\n";
                        $text .= $tmp;
                        break;

                    // set leading
                    case 'TL':
                        break;

                    case 'Tm':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if ($current_position['y'] !== false && floatval($y) != floatval($current_position['y'])) {
                            $text .= "\n";
                        }
                        $current_position = array('x' => $x, 'y' => $y);
                        break;

                    case 'Tw':
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

                    // move to start of next line
                    case 'T*':
                        $text .= "\n";
                        break;

                    default:
                        //throw new \Exception('Operator not supported: ' . $command['operator']);
                }
            }
            //echo $text . "\n";
            //$text = '';
            //echo "------------------------------------\n";
        }

        return $text;
    }

    /**
     * @param string $hexa
     * @param Font   $font
     *
     * @return string
     */
    protected function decodeHexadecimal($hexa, Font $font = null)
    {
        $text = '';

        $matches = array();
        $regexp  = '/<(?<data>[a-z0-9]+)>\s*(?<position>[\-0-9\.]*)/mis';
        preg_match_all($regexp, $hexa, $matches);

        foreach ($matches['data'] as $pos => $hexa) {
            for ($i = 0; $i < strlen($hexa); $i = $i + 4) {
                if (!is_null($font)) {
                    $new_char = $font->translateChar(substr($hexa, $i, 4), true);
                    $text .= $new_char; //pack('H*', $hexa_char);
                }
            }

            if ((int)$matches['position'][$pos] < 0) {
                $text .= ' ';
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @param Font   $font
     *
     * @return string
     */
    protected function decodeOctale($text, Font $font = null)
    {
        $found_octal_values = array();
        preg_match_all('/\\\\([0-9]{3})/', $text, $found_octal_values);
        foreach ($found_octal_values[0] as $value) {
            $octal = substr($value, 1);

            // TODO : fixit
            if (intval($octal) < 40 && false) {
                // Skips non printable chars
                $text = str_replace($value, '', $text);
            } else {
                $text = str_replace($value, chr(octdec($octal)), $text);
            }
        }

        return $text;
    }

    /**
     * @param string $text
     * @param Font   $font
     *
     * @return string
     */
    public function decodeText($text, Font $font = null)
    {
        $text = $this->decodeOctale($text);

        $result        = '';
        $cur_start_pos = 0;

        while (($cur_start_text = mb_strpos($text, '(', $cur_start_pos)) !== false) {
            // New text element found
            if ($cur_start_text - $cur_start_pos > 8) {
                $spacing = '';
            } else {
                $spacing_size = floatval(trim(mb_substr($text, $cur_start_pos, $cur_start_text - $cur_start_pos)));

                // TODO : use matrix to determine spacing
                if ($spacing_size < -50) {
                    $spacing = ' ';
                } else {
                    $spacing = '';
                }
            }
            $cur_start_text++;

            $start_search_end = $cur_start_text;
            while (($cur_start_pos = mb_strpos($text, ')', $start_search_end)) !== false) {
                $cur_extract = mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text);
                preg_match('/(?<escape>[\\\]*)$/s', $cur_extract, $match);
                if (!(mb_strlen($match['escape']) % 2)) {
                    break;
                }
                $start_search_end = $cur_start_pos + 1;
            }

            // something wrong happened
            if ($cur_start_pos === false) {
                break;
            }

            // extract content
            $sub_text = mb_substr($text, $cur_start_text, $cur_start_pos - $cur_start_text);
//            var_dump('avant', $sub_text);
            $sub_text = str_replace(array('\\\\', '\(', '\)', '\n', '\r'), array('\\', '(', ')', "\n", "\r"), $sub_text);

            // decode content
            if (!is_null($font)) {
//                var_dump('decode');
                $sub_text = $this->decodeContent($sub_text, $font);
            }
//            var_dump('apres', $sub_text);
//            var_dump('ajout espace', $spacing, $spacing_size);

            // add content to result string
            $result .= $spacing . $sub_text;
            $cur_start_pos++;
        }

        return $result;
    }

    protected function decodeContent($text, Font $font)
    {
        if (!$font->getToUnicode() instanceof ElementMissing) {
            $chars  = preg_split('//', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $result = '';

            foreach ($chars as $char) {
//                var_dump(ord($char));
                $decoded = $font->translateChar($char);
                $result .= $decoded;
            }

            return $result;
        } elseif ($encoding = $font->getHeader()->get('Encoding')->getContent()) {
            if (preg_match('/^mac/i', $encoding)) {
                if ($decoded = @iconv('MacRoman', 'UTF-8//TRANSLIT//IGNORE', $text)) {
                    return $decoded;
                }
            }
        }

        return $text;
    }

    /**
     * @return array
     */
    public function getTextParts()
    {
        $regexp  = '/(BT\s*)(.*?)(\s*ET)/ms';
        $matches = array();

        //var_dump($this->header->getElements());

        preg_match_all($regexp, $this->content, $matches);

        return $matches[2];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->getContent());
    }

    /**
     * @param string $text_part
     *
     * @return array
     */
    protected function getCommandsFromTextPart($text_part)
    {
        $regex   = '#(\[.*?\]\s*)(TJ)|(\(.*?\)\s*)(Tj)|(<.*?>\s*)(Tj)|(T\*)|((/[A-Za-z0-9]|[0-9\.])+\s+)+([a-zA-Z]{1,2})#s';
        $matches = array();

        if (preg_match_all($regex, $text_part, $matches)) {
            $commands = array();

            foreach ($matches[0] as $pos => $command) {
                preg_match('/^\s*(.*?)\s*([A-Z\*]+)\s*$/is', $command, $sub_match);

                $command  = $sub_match[1];
                $operator = $sub_match[2];

                $commands[] = array(
                    'operator' => $operator,
                    'command'  => $command,
                );
            }

            return $commands;
        } else {
            return array();
        }
    }

    /**
     * @param Document $document
     * @param string   $content
     *
     * @return Font|Image|Object|Page
     */
    public static function parse(Document $document, $content = '')
    {
        //
        if (preg_match('/^(<<|\[)/s', $content, $matches)) {
            $position = 0;
            $header   = Header::parse($content, $document, $position);
            $content  = trim(substr($content, $position), " \n\r");
        } else {
            $header   = new Header(array(), $document);
            $content  = trim($content, " \n\r");
        }

        $matches = array();
        /*if (preg_match('/^((<<)(.*)(>>[\n\r]{0,2}))?(.*)/s', $content, $matches)) {
            //echo 'header: "' . $matches[3] . "\"\n";

            $header  = Header::parse($matches[3], $document);
            $content = trim($matches[5]);
        } else {
            var_dump($content);
            die('no header');
        }*/

        if (preg_match('/^stream[\n\r]{1,2}(?<data>.*?)[\n\r]{1,2}endstream$/s', $content, $matches)) {
            $content = $matches['data'];
            //echo 'extracted from stream' . "\n";
            //$content = substr($content, strpos($content, 'stream') + strlen('stream') + $nb_chars, strrpos($content, 'endstream') - strlen('stream') - $nb_chars);
        }

        //var_dump($header);

        if ($header->has('Filter') && $header->get('Filter')->contains('FlateDecode')) {
            //echo '"' . $content . '"';
            $decoded = @gzuncompress($content);
            if ($decoded !== false) {
                $content = $decoded;
                //echo 'compressed' . "\n";
            } else {
                //echo 'not compressed 1' . "\n";
            }
        } else {
            //echo 'not compressed 2' . "\n";
        }

        //echo 'content: ' . $content . "\n";
        //die();

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
