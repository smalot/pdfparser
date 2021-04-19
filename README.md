# PdfParser #

Pdf Parser, a standalone PHP library, provides various tools to extract data from a PDF file.

![CI](https://github.com/smalot/pdfparser/workflows/CI/badge.svg)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smalot/pdfparser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smalot/pdfparser/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/smalot/pdfparser/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/smalot/pdfparser/?branch=master)
[![License](https://poser.pugx.org/smalot/pdfparser/license)](//packagist.org/packages/smalot/pdfparser)

[![Latest Stable Version](https://poser.pugx.org/smalot/pdfparser/v)](//packagist.org/packages/smalot/pdfparser)
[![Total Downloads](https://poser.pugx.org/smalot/pdfparser/downloads)](//packagist.org/packages/smalot/pdfparser)
[![Monthly Downloads](https://poser.pugx.org/smalot/pdfparser/d/monthly)](//packagist.org/packages/smalot/pdfparser)
[![Daily Downloads](https://poser.pugx.org/smalot/pdfparser/d/daily)](//packagist.org/packages/smalot/pdfparser)

Website : [https://www.pdfparser.org](https://www.pdfparser.org/?utm_source=GitHub&utm_medium=website&utm_campaign=GitHub)

Test the API on our [demo page](https://www.pdfparser.org/demo).

This project is supported by [Actualys](http://www.actualys.com).

## Features ##

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

**This Library is under active maintenance.**
There is no active development by the author of this library (at the moment), but we welcome any pull request adding/extending functionality!

## Documentation ##

[Read the documentation on website](http://www.pdfparser.org/documentation?utm_source=GitHub&utm_medium=documentation&utm_campaign=GitHub).

Original PDF References files can be downloaded from this url: http://www.adobe.com/devnet/pdf/pdf_reference_archive.html

**For developers**: Please read [DEVELOPER.md](DEVELOPER.md) for more information about local development of the PDFParser library.

## Installation

### Using Composer

* Obtain [Composer](https://getcomposer.org)
* Run `composer require smalot/pdfparser`

### Use alternate file loader

In case you can't use Composer, you can include `alt_autoload.php-dist` into your project.
It will load all required files at once.
Afterwards you can use `PDFParser` class and others.

## License ##

This library is under the [LGPLv3 license](https://github.com/smalot/pdfparser/blob/master/LICENSE.txt).
