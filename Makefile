COVERAGE_THRESHOLD ?= 80

install:
	composer install

lint:
	vendor/bin/phpcs --standard=PSR12 src bin && \
	vendor/bin/phpstan analyse

test:
	vendor/bin/phpunit --testdox

test-coverage:
	mkdir -p tests/coverage
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover=tests/coverage/clover.xml --testdox
	COVERAGE_THRESHOLD=$(COVERAGE_THRESHOLD) php bin/check-coverage
