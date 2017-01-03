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

namespace Smalot\PdfParser\Tests\Units;

use mageekguy\atoum;

/**
 * Class Parser
 *
 * @package Smalot\PdfParser\Tests\Units
 */
class Parser extends atoum\test
{
    public function testParseFile()
    {
        $directory = getcwd() . '/samples/bugs';

        if (is_dir($directory)) {
            $files  = scandir($directory);
            $parser = new \Smalot\PdfParser\Parser();

            foreach ($files as $file) {
                if (preg_match('/^.*\.pdf$/i', $file)) {
                    try {
                        $document = $parser->parseFile($directory . '/' . $file);
                        $pages    = $document->getPages();
                        $page     = $pages[0];
                        $content  = $page->getText();
                        $this->assert->string($content);
                    } catch (\Exception $e) {
                        if ($e->getMessage() != 'Secured pdf file are currently not supported.' && strpos($e->getMessage(), 'TCPDF_PARSER') != 0) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
}
