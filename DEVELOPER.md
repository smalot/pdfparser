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
