# Вычислитель отличий (PHP)

[![hexlet-check](https://github.com/jsteacat/php-project-48/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/jsteacat/php-project-48/actions)
[![CI](https://github.com/jsteacat/php-project-48/actions/workflows/ci.yml/badge.svg)](https://github.com/jsteacat/php-project-48/actions/workflows/ci.yml)

В этом проекте отрабатывается работа с коллекциями. Изучаются способы построения и обхода деревьев. Вы познакомитесь с разными форматами данных (json, yml), научитесь их парсить и формировать. Начнете писать тесты (PHPUnit) и освоите разработку через них. Познакомитесь с непрерывной интеграцией (CI) и элементами экстремального программирования (XP). Прокачаете ООП мышление.

Учебный проект Хекслета: https://ru.hexlet.io/programs/php
Как это должно работать: https://asciinema.org/a/Pe6QypnLEmFWssNAjCOJN1iii

## Стек

- PHP >= 8.4
- [docopt/docopt](https://github.com/docopt/docopt.php) — разбор аргументов командной строки
- [symfony/yaml](https://symfony.com/doc/current/components/yaml.html) — разбор YAML
- PHPUnit, PHPStan, PHP_CodeSniffer (PSR-12) — только для разработки

## Требования

- PHP 8.4 или новее
- Composer

## Установка

```bash
git clone https://github.com/jsteacat/php-project-48.git
cd php-project-48
composer install
```

## Использование

### CLI

```bash
# Сравнение двух JSON-файлов
./bin/gendiff tests/fixtures/file1.json tests/fixtures/file2.json

# Сравнение двух YAML-файлов
./bin/gendiff tests/fixtures/file1.yaml tests/fixtures/file2.yml
```

Опции:

| Опция                      | Описание                                              |
|----------------------------|-------------------------------------------------------|
| `-h`, `--help`             | Показать справку                                      |
| `-v`, `--version`          | Показать версию                                       |
| `-f <fmt>`, `--format <fmt>` | Формат вывода, по умолчанию `stylish`               |

Поддерживаются файлы в форматах `.json`, `.yaml` и `.yml`. Из форматов вывода пока реализован только `stylish`.

Пример вывода:

```
{
  - follow: false
  host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
```

Если файл не найден, его формат не поддерживается или содержимое повреждено, сообщение об ошибке выводится в поток ошибок (`stderr`), а код возврата процесса равен `1`.

### Как библиотека

```php
use function Gendiff\genDiff;

// третий аргумент — формат вывода, по умолчанию stylish
$diff = genDiff('path/to/file1.json', 'path/to/file2.json');
echo $diff;
```

## Разработка

```bash
make install        # установка зависимостей (composer install)
make lint           # проверка стиля (phpcs, PSR-12) и статический анализ (phpstan)
make test           # запуск тестов PHPUnit
make test-coverage  # тесты с расчётом покрытия и проверкой порога
```

Порог покрытия по умолчанию — 80%, его можно переопределить:

```bash
COVERAGE_THRESHOLD=90 make test-coverage
```

---

<details>
<summary>Автоматические тесты Хекслета</summary>

Тесты запускаются на каждый коммит. За запуск отвечает файл `.github/workflows/hexlet-check.yml` — не удаляйте и не переименовывайте ни его, ни репозиторий.

</details>

## О Хекслете

[Хекслет](https://ru.hexlet.io/) — школа программирования: авторские программы обучения с практикой, поддержкой наставников и реальными проектами, которые остаются в резюме. Этот репозиторий — один из таких проектов.
