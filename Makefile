install-dev-tools:
	composer update --working-dir=dev-tools

# Workaround to force PHPUnit 7.5.x when running Scrutinizer.
# Scrutinizer fails due to not enough memory when using a newer PHPUnit version (tested with 9.5).
# @see: https://github.com/smalot/pdfparser/issues/410
# @see: https://github.com/smalot/pdfparser/pull/412
prepare-for-scrutinizer:
	cd dev-tools && sed -e 's/>=7.5/^7.5/g' composer.json > composer.json2 && rm composer.json && mv composer.json2 composer.json

run-php-cs-fixer:
	dev-tools/vendor/bin/php-cs-fixer fix $(ARGS)

run-phpstan:
	dev-tools/vendor/bin/phpstan analyze $(ARGS)

run-phpunit:
	dev-tools/vendor/bin/phpunit $(ARGS)
