# Порог покрытия: ниже него make test-coverage падает,
# а вместе с ним краснеет шаг покрытия в CI и бейджик GitHub Actions
COVERAGE_MIN ?= 80

install:
	composer install

# Проверка composer.json
validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin
	composer exec --verbose phpstan -- analyse

test:
	vendor/bin/phpunit --testdox

test-coverage:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover=build/logs/clover.xml --testdox
	@php -r '$$m = simplexml_load_file("build/logs/clover.xml")->project->metrics; $$total = (int) $$m["statements"]; $$covered = (int) $$m["coveredstatements"]; $$pct = $$total > 0 ? $$covered / $$total * 100 : 100.0; printf("Total coverage: %.2f%% (min %d%%)\n", $$pct, $(COVERAGE_MIN)); exit($$pct + 1e-9 < $(COVERAGE_MIN) ? 1 : 0);'
