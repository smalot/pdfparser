<?php

namespace Smalot\PdfParser;

/**
 * Class Parser
 * @package PdfParser
 */
class Parser
{
    /**
     * Parse PDF file
     *
     * @param string $filename
     *
     * @return string
     */
    public static function parseFile($filename)
    {
        $pdf = Document::parseFile($filename);

        return self::extractText($pdf);
    }

    /**
     * Parse PDF content
     *
     * @param string $content
     *
     * @return string
     */
    public static function parseContent($content)
    {
        $pdf = Document::parseContent($content);

        return self::extractText($pdf);
    }

    /**
     * Convert a PDF into text.
     *
     * @return string The extracted text from the PDF
     */
    protected static function extractText(Document $pdf)
    {
        $start = microtime(true);
        echo 'parsing : ' . round((microtime(true) - $start) * 1000) . " ms\n";
        /*$dictionary = $pdf->getDictionary();

        foreach ($dictionary as $type => $ids) {
            echo '/Type /' . $type . "\n";

            foreach ($ids as $id) {
                $object  = $pdf->getObjectById($id);
                $subtype = $object->getHeader()->get('Subtype')->getContent();
                if ($subtype) {
                    echo '    /Subtype /' . $subtype . "\n";
                }
            }
        }*/
//        echo $pdf->getObjectById(42)->getContent();
        /*var_dump($pdf->getObjectById(1)->getHeader()->get('Pages')->getHeader());
        die("done");*/

        //$images = $pdf->getObjectsByType('XObject', 'Image');
        //var_dump($images[8]->getHeader());
        //file_put_contents('8.jpg', $images[8]->getContent());

        //var_dump($params->getHeader());
        //var_dump($params->getContent());

        //die();

        /*foreach ($images as $id => $image) {
            var_dump($image->getHeader());
            file_put_contents($id . '.jpg', $image->getContent());
        }
        die();*/

        $text  = '';
        $pages = $pdf->getPages();
        echo 'extraction des pages : ' . round((microtime(true) - $start) * 1000) . " ms\n";
        foreach ($pages as $pos => $page) {
            //if ($pos<7) continue;
            //echo 'page #' . $pos . "\n";
            $fonts = $page->getFonts();
            foreach ($fonts as $id => $font) {
                echo ' - font' . "\n";
                //var_dump($id, $font->getHeader());
                /*if ($toUnicode = $font->getToUnicode()) {
                    var_dump($toUnicode->getContent());
                }*/
            }
            //die();
            $text_page = $page->getText();
            echo 'page #' . ($pos + 1) . ' : ' . round((microtime(true) - $start) * 1000) . " ms\n";

            //var_dump('content text', $text_page);
            $text .= $text_page . "\n\n";
//            echo 'une seule page';
//            break;
            //if ($pos>=7) break;
        }

        return $text;
    }
}
