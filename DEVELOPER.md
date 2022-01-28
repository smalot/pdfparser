# Developers

## .editorconfig

Please make sure your editor uses our `.editorconfig` file. It contains rules about our coding styles.

## Development Tools and Tests

Our test related files are located in `tests` folder.
Tests are written using PHPUnit.

To install (and update) development tools like PHPUnit or PHP-CS-Fixer run:

> make install-dev-tools

Development tools are getting installed in `dev-tools/vendor`.
Please check `dev-tools/composer.json` for more information about versions etc.
To run a tool manually you use `dev-tools/vendor/bin`, for instance:

> dev-tools/vendor/bin/php-cs-fixer fix --verbose --dry-run

Below are a few shortcuts to improve your developer experience.

### PHPUnit

To run all tests run:

> make run-phpunit

### PHP-CS-Fixer

To check coding styles run:

> make run-php-cs-fixer

### PHPStan

To run a static code analysis use:

> make run-phpstan

## Base64 encoded PDFs

If working with [Base64](https://en.wikipedia.org/wiki/Base64) encoded PDFs you might want to parse the PDF without saving the file on disk. This sample will parse the Base64 encoded PDF and extract text from each page.

```php
<?php
// Include Composer autoloader if not already done.
include "vendor/autoload.php";

// Parse Base64 encoded PDF string and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseContent(base64_decode($base64PDF));

$text = $pdf->getText();
echo $text;
```
