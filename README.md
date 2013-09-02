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

State
=========

This Library is still under active development. As a result, users must expect BC breaks when using the master version.

Todo list :
- [ ] Complete Unit Tests
- [ ] Support of hexa and octal encoding in properties
- [ ] Add helper to extract meta data
- [ ] Clean code on Font Classes
- [ ] Support of encoding files

Documentation
=========

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

[Read the Documentation](https://github.com/smalot/pdfparser/blob/master/doc)

Installation
=========

All the installation instructions are located in the [documentation](https://github.com/smalot/pdfparser/blob/master/doc).

License
=========

This bundle is under the [GPLv2 license](https://github.com/smalot/pdfparser/blob/master/LICENSE). See the complete license in the bundle:

    LICENSE
