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
        $text                = '';
        $text_parts          = $this->getTextParts();
        $current_font        = null;
        $current_font_size   = 0;
        $current_position_td = array('x' => false, 'y' => false);
        $current_position_tm = array('x' => false, 'y' => false);

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
                        if ((floatval($x) <= 0 && floatval($y) > 0) ||
                            ($current_position_td['y'] !== false && floatval($y) != floatval($current_position_td['y']))
                        ) {
                            $text .= "\n";
                        }
                        $current_position_td = array('x' => $x, 'y' => $y);
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
                            $text .= $current_font->decodeHexadecimal($tmp);
                        } else {
                            $text .= $current_font->decodeText($tmp);
                        }
                        break;

                    case 'Tj':
                        if ($command['command'][0] == '<') {
                            $command['command'] = '(' . $command['command'] . ')';
                        }
                        $tmp = $current_font->decodeText($command['command']);
                        $text .= $tmp;
                        break;

                    // set leading
                    case 'TL':
                        break;

                    case 'Tm':
                        $args = preg_split('/\s/s', $command['command']);
                        $y    = array_pop($args);
                        $x    = array_pop($args);
                        if ($current_position_tm['y'] !== false && floatval($y) != floatval(
                                $current_position_tm['y']
                            )
                        ) {
                            $text .= "\n";
                        }
                        $current_position_tm = array('x' => $x, 'y' => $y);
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
        }

        return $text;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)($this->getContent());
    }

    /**
     * @return array
     */
    protected function getTextParts()
    {
        $regexp  = '/(BT\s*)(.*?)(\s*ET)/ms';
        $matches = array();

        preg_match_all($regexp, $this->content, $matches);

        return $matches[2];
    }

    /**
     * @param string $text_part
     *
     * @return array
     */
    protected function getCommandsFromTextPart($text_part)
    {
        $regex    = '#(\[.*?\]\s*)(TJ)|(\(.*?\)\s*)(Tj)|(<.*?>\s*)(Tj)|(T\*)|((/[A-Za-z0-9]|[0-9\.])+\s+)+([a-zA-Z]{1,2})#s';
        $matches  = array();
        $commands = array();

        if (preg_match_all($regex, $text_part, $matches)) {

            foreach ($matches[0] as $pos => $command) {
                preg_match('/^\s*(.*?)\s*([A-Z\*]+)\s*$/is', $command, $sub_match);

                $command  = $sub_match[1];
                $operator = $sub_match[2];

                $commands[] = array(
                    'operator' => $operator,
                    'command'  => $command,
                );
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
    public static function parse(Document $document, $content = '')
    {
        $matches = array();

        //
        if (preg_match('/^\s*<</s', $content, $matches)) {
            $position = 0;
            $header   = Header::parse($content, $document, $position);
            $content  = trim(substr($content, $position), " \n\r");
        } else {
            $header  = new Header(array(), $document);
            $content = trim($content, " \n\r");
        }

        if (preg_match('/^stream[\n\r]{0,2}(?<data>.*?)[\n\r]{0,2}endstream.*$/s', $content, $matches)) {
            $content = $matches['data'];
            //echo 'extracted from stream' . "\n";
            //$content = substr($content, strpos($content, 'stream') + strlen('stream') + $nb_chars, strrpos($content, 'endstream') - strlen('stream') - $nb_chars);
        }

        //var_dump($header);

        if ($header->has('Filter')) {
            $filters = (array)($header->get('Filter')->getContent());

            foreach ($filters as $filter) {
//                echo 'apply filter: ' . $filter->getContent() . "\n";
                try {
//                    echo 'length (before "'.$filter.'"):' . strlen($content) . "\n";
                    $content = Filters::decodeFilter((string)$filter, $content);
//                var_dump($content);
                } catch (\Exception $e) {
//                    echo 'error: ' . $e->getMessage() . "\n";
                    trigger_error($e->getMessage());
                    $content = '';
                    break;
                }
            }
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
