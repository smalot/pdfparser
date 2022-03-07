# Configuring the behavior of the parser

To change the behavior of the parser, create a `Config` object and pass it to the parser.
In this case, we're setting the font space limit.
Changing this value can be helpful when `getText()` returns a text with too many spaces.

```php
$config = new \Smalot\PdfParser\Config();
$config->setFontSpaceLimit(-60);
$parser = new \Smalot\PdfParser\Parser([], $config);
$pdf = $parser->parseFile('document.pdf');
// output extracted text
// echo $pdf->getText();
```

## Config options overview

The `Config` class has the following options:

| Option                   | Type    | Default         | Description                                                                                                                                          |
|--------------------------|---------|-----------------|------------------------------------------------------------------------------------------------------------------------------------------------------|
| `setDecodeMemoryLimit`   | Integer | `0`             | If parsing fails because of memory exhaustion, you can set a lower memory limit for decoding operations.                                             |
| `setFontSpaceLimit`      | Integer | `-50`           | Changing font space limit can be helpful when `Parser::getText()` returns a text with too many spaces.                                               |
| `setHorizontalOffset`    | String  | ` `             | When words are broken up or when the structure of a table is not preserved, you may get better results when adapting `setHorizontalOffset`.          |
| `setPdfWhitespaces`      | String  | `\0\t\n\f\r `   |                                                                                                                                                      |
| `setPdfWhitespacesRegex` | String  | `[\0\t\n\f\r ]` |                                                                                                                                                      |
| `setRetainImageContent`  | Boolean | `true`          | If parsing fails because of memory exhaustion, you can set the value to `false`. It wont retain image content anymore, but will use less memory too. |


## option setDecodeMemoryLimit + setRetainImageContent (manage memory usage)

If parsing fails because of memory exhaustion, you can use the following options.

```php
$config = new \Smalot\PdfParser\Config();
// Whether to retain raw image data as content or discard it to save memory
$config->setRetainImageContent(false);
// Memory limit to use when de-compressing files, in bytes
$config->setDecodeMemoryLimit(1000000);
$parser = new \Smalot\PdfParser\Parser([], $config);
```

## option setHorizontalOffset

When words are broken up or when the structure of a table is not preserved, you can use `setHorizontalOffset`.

```php
$config = new \Smalot\PdfParser\Config();
// An empty string can prevent words from breaking up
$config->setHorizontalOffset('');
// A tab can help preserve the structure of your document
$config->setHorizontalOffset("\t");
$parser = new \Smalot\PdfParser\Parser([], $config);
```

## option setFontSpaceLimit

Changing font space limit can be helpful when `getText()` returns a text with too many spaces.

```php
$config = new \Smalot\PdfParser\Config();
$config->setFontSpaceLimit(-60);
$parser = new \Smalot\PdfParser\Parser([], $config);
$pdf = $parser->parseFile('document.pdf');
```
