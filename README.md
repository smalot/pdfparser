PdfParser
=========

[![Build Status](https://travis-ci.org/smalot/pdfparser.png?branch=master)](https://travis-ci.org/smalot/pdfparser)
[![Total Downloads](https://poser.pugx.org/smalot/pdfparser/downloads.png)](https://packagist.org/packages/smalot/pdfparser)

PdfParser, a standalone PHP library, provides various tools to extract data from a PDF file.
Features included :

- Load/parse objects and headers
- Extract meta data (author, description, ...)
- Extract text from ordered pages
- Support of compressed pdf
- Support of MAC OS Roman charset encoding
- Handling of hexa and octal encoding in text sections
- PSR-0 compliant ([autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md))
- PSR-1 compliant ([code styling](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md))

Currently, secured documents are not supported.

This project is supported by [Actualys](http://www.actualys.com).

State
=====

This Library is still under active development. As a result, users must expect BC breaks when using the master version.

Todo list :
- [ ] Complete Unit Tests
- [ ] Support of hexa and octal encoding in properties
- [ ] Add helper to extract meta data
- [ ] Clean code on Font Classes
- [ ] Support of encoding files
- [X] Support of missing cross-reference table
- [X] Support of missing endstream tag (malformed file)

Documentation
=============

Extract text from PDF File :
```php
$text = \Smalot\PdfParser\Parser::parseFile('document.pdf');
```

Extract text from the second page :
```php
$document = \Smalot\PdfParser\Document::parseFile('document.pdf');
$pages    = $document->getPages();
$text     = $pages[1]->getText();
```

Run Atoum unit tests (with code coverage - if xdebug installed) :
```bash
#> vendor/bin/atoum -d src/Smalot/PdfParser/Tests/
```

[Read the Documentation](https://github.com/smalot/pdfparser/blob/master/doc)

Installation
============

To run PDfParser as a standalone library, you can use [composer](http://getcomposer.org/download/).

```bash
#> composer install
```

This command will download Atoum library and generate the following file :

```
vendor/autoload.php
```

Test
====

Create a 'sample.php' file :

```php
<?php

include 'vendor/autoload.php';

$filename = 'document.pdf';
$text = \Smalot\PdfParser\Parser::parseFile($file);

echo $text;
```


License
=======

This library is under the [GPLv2 license](https://github.com/smalot/pdfparser/blob/master/LICENSE). See the complete license in the bundle:

