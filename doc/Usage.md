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

## Extract text positions

You can extract transformation matrix (indexes 0-3) and x,y position of text objects (indexes 4,5).

```php
$data = $pdf->getPages()[0]->getDataTm();

Array
(
    [0] => Array
        (
            [0] => Array
                (
                    [0] => 0.999429
                    [1] => 0
                    [2] => 0
                    [3] => 1
                    [4] => 201.96
                    [5] => 720.68
                )

            [1] => Document title
        )

    [1] => Array
        (
            [0] => Array
                (
                    [0] => 0.999402
                    [1] => 0
                    [2] => 0
                    [3] => 1
                    [4] => 70.8
                    [5] => 673.64
                )

            [1] => Calibri : Lorem ipsum dolor sit amet, consectetur a
        )
)
```

When activated via Config setting (`Config::setDataTmFontInfoHasToBeIncluded(true)`) font identifier (index 2) and font size (index 3) are added to dataTm.

```php
// create config
$config = new Smalot\PdfParser\Config();
$config->setDataTmFontInfoHasToBeIncluded(true);

// use config and parse file
$parser = new Smalot\PdfParser\Parser([], $config);
$pdf = $parser->parseFile('document.pdf');

$data = $pdf->getPages()[0]->getDataTm();

Array
(
    [0] => Array
        (
            [0] => Array
                (
                    [0] => 0.999429
                    [1] => 0
                    [2] => 0
                    [3] => 1
                    [4] => 201.96
                    [5] => 720.68
                )

            [1] => Document title
            [2] => R7
            [3] => 27.96
        )

    [1] => Array
        (
            [0] => Array
                (
                    [0] => 0.999402
                    [1] => 0
                    [2] => 0
                    [3] => 1
                    [4] => 70.8
                    [5] => 673.64
                )

            [1] => Calibri : Lorem ipsum dolor sit amet, consectetur a
            [2] => R9
            [3] => 11.04
        )
)
```

Text width should be calculated on text from dataTm to make sure all character widths are available.
In next example we are using data from above.

```php
$fonts = $pdf->getFonts();
$font_id = $data[0][2]; //R7
$font = $fonts[$font_id];
$text = $data[0][1];
$width = $font->calculateTextWidth($text, $missing);
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

## Calculate text width

Try to calculate text width for given font.
Characters without width are added to `$missing` array in second parameter.

```php
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile('document.pdf');
// get first font (we assume here there is at least one)
$font = reset($pdf->getFonts());
// get width
$width = $font->calculateTextWidth('Some text', $missing);
```
