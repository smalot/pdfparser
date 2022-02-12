# PDF parser

[![Version](https://poser.pugx.org/smalot/pdfparser/v)](//packagist.org/packages/smalot/pdfparser)
![CI](https://github.com/smalot/pdfparser/workflows/CI/badge.svg)
[![Downloads](https://poser.pugx.org/smalot/pdfparser/downloads)](//packagist.org/packages/smalot/pdfparser)

The `smalot/pdfparser` is a standalone PHP package that provides various tools to extract data from PDF files.

This Library is under **active maintenance**. There is no active development by the author of this library (at the
moment), but we welcome any pull request adding/extending functionality!

## Install

This library requires PHP 7.1+ since [v1](https://github.com/smalot/pdfparser/releases/tag/v1.0.0). You can install the
package via composer:

```bash
compose require smalot/pdfparser
```

In case you can't use composer, you can include `alt_autoload.php-dist`.

## Usage

First create a parser object and point it to a file.

```php
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile('document.pdf');

// or:
$pdf = $parser->parseContent(file_get_contents('document.pdf'))
 ```

A common scenario is to extract text.

```php
$text = $pdf->getText();

// or extract the text of a specific page
$text = $pdf->getPages()[0]->getText();
```

You can also extract metadata. The available data varies from pdf to pdf.

```php
$metaData = $pdf->getDetails();

Array
(
    [Producer] => Adobe Acrobat
    [CreatedOn] => 2022-01-28T16:36:11+00:00
    [Pages] => 35
)
```

### Configuring the behavior of the parser

To change the behavior the parser, create a config object and pass it to the parser.
In this case, we're setting the font space limit.
Changing this value can be helpful when `getText()` returns a text with too many spaces.

```php
$config = new \Smalot\PdfParser\Config();
$config->setFontSpaceLimit(-60);
$parser = new \Smalot\PdfParser\Parser([], $config);
$pdf = $parser->parseFile('document.pdf');
```

When words are broken up or when structure of a table is not preserved, you can use `setHorizontalOffset`.

```php
$config->setFontSpaceLimit(""); // an empty string can prevent words from breaking up
$config->setFontSpaceLimit("\t"); // a tab can help preserve the structure of your document
```

To manage memory usage you can use the following options.
```php
$config->setRetainImageContent(false); // Whether to retain raw image data as content or discard it to save memory
$config->setDecodeMemoryLimit(1000000); //  Memory limit to use when de-compressing files, in bytes.
```

## License

This library is under the [LGPLv3 license](https://github.com/smalot/pdfparser/blob/master/LICENSE.txt).
