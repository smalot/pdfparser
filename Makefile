install-dev-tools:
	composer update --working-dir=dev-tools

run-php-cs-fixer:
	dev-tools/vendor/bin/php-cs-fixer fix

run-phpstan:
	dev-tools/vendor/bin/phpstan/phpstan/phpstan analyse

run-phpunit:
	dev-tools/vendor/bin/phpunit
