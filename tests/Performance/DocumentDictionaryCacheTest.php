<?php

/**
 * @file This file is part of the PdfParser library.
 *
 * @author  Konrad Abicht <k.abicht@gmail.com>
 * @date    2020-06-01
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

namespace Tests\Smalot\PdfParser\Performance;

use Exception;
use Smalot\PdfParser\Element;
use Smalot\PdfParser\Encoding;

/**
 * This test checks does a performance test with certain PDF files that extensively use
 * the getFirstFont() method of Document.php. If Document.php correctly uses a dictionary
 * to cache the objects inside the PDF file, then the parsing should be quick.
 * If it does not, the parsing can be extensively slow or even crash.
 */
class DocumentDictionaryCacheTest extends AbstractPerformanceTest
{

    public function run()
    {
        $parser = new \Smalot\PdfParser\Parser();

		// load PDF file content
		$data = file_get_contents('https://comserv.cs.ut.ee/home/files/Shoush_ComputerScience_2020.pdf?study=ATILoputoo&reference=76F6FAFD4C9E6981D9A434D32D2E7EE2AE9C49E7');

		// give PDF content to function and parse it
		$pdf = $parser->parseContent($data); 

		$pages = $pdf->getPages();

		foreach ($pages as $i => $page) { /** @var $page Page */
			if ($i < 77) continue;
			if ($i > 78) continue;

			$startTime = microtime(true);
			$pageText = $page->getText();
			$endTime = microtime(true);
			
			echo '<b>Page ' . $i . ' (took ' . ($endTime - $startTime) . ' seconds, ' . round(memory_get_usage() / (1000 * 1000), 2) . ' MB RAM)</b><br>';
			var_dump($pageText);
			echo '<br><br>';
		}
    }

}
