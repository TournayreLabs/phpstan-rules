.PHONY: install phpstan phpunit run-all-tests-php

install:
	composer install

phpstan:
	vendor/bin/phpstan analyse --memory-limit=512M

phpunit:
	vendor/bin/phpunit

run-all-tests-php: phpstan phpunit
