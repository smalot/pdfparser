# Usage

First create a parser object and point it to a file.

```php
$parser = new \Smalot\PdfParser\Parser();

$pdf = $parser->parseFile('document.pdf');
// .. or ...
$pdf = $parser->parseContent(file_get_contents('document.pdf'))
 ```

## Extract text

A common scenario is to extract text.

```php
$text = $pdf->getText();

// or extract the text of a specific page (in this case the first page)
$text = $pdf->getPages()[0]->getText();
```

## Extract metadata

You can also extract metadata. The available data varies from PDF to PDF.

```php
$metaData = $pdf->getDetails();

Array
(
    [Producer] => Adobe Acrobat
    [CreatedOn] => 2022-01-28T16:36:11+00:00
    [Pages] => 35
)
```

## Read Base64 encoded PDFs

If working with [Base64](https://en.wikipedia.org/wiki/Base64) encoded PDFs, you might want to parse the PDF without saving the file to disk.
This sample will parse the Base64 encoded PDF and extract text from each page.

```php
<?php
// Parse Base64 encoded PDF string and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseContent(base64_decode($base64PDF));

$text = $pdf->getText();
echo $text;
```
