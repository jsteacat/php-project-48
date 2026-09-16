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

Поддерживаются файлы в форматах `.json`, `.yaml` и `.yml`. Из форматов вывода пока реализован только `stylish`: неизвестный формат — это ошибка с кодом возврата `1`.

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

## Структура проекта

```
bin/gendiff           — CLI-входная точка
src/functions.php     — публичное API: genDiff(), сборка diff, выбор форматтера
src/Constants.php     — форматы вывода, поддерживаемые расширения, типы узлов diff
src/Parser.php        — чтение json / yaml / yml
src/Runner.php        — разбор аргументов и вывод результата
src/Formatters/       — форматы вывода: каждый вариант — отдельный класс
src/Exceptions/       — исключения проекта
tests/                — PHPUnit-тесты, tests/fixtures — фикстуры
```

Сравнение и вывод разделены: `buildDiff()` собирает различия в промежуточное представление,
а форматтер превращает их в текст. Чтобы добавить формат (`plain`, `json`), достаточно
нового класса в `src/Formatters` и ветки в `createFormatter()`.

## Разработка

```bash
make install        # установка зависимостей (composer install)
make validate       # проверка composer.json (composer validate)
make lint           # проверка стиля (phpcs, PSR-12) и статический анализ (phpstan)
make test           # запуск тестов PHPUnit
make test-coverage  # тесты с расчётом покрытия и проверкой порога COVERAGE_MIN
```

Порог покрытия задаётся переменной `COVERAGE_MIN` в `Makefile` (по умолчанию 80%), его можно переопределить:

```bash
COVERAGE_MIN=90 make test-coverage
```

Отчёт о покрытии сохраняется в `build/logs/clover.xml`. Для подсчёта нужен Xdebug или PCOV. Вспомогательные файлы тестов (фикстуры) лежат в `tests/fixtures`.

На каждый push и pull request в `main` запускается [воркфлоу CI](.github/workflows/ci.yml): установка зависимостей, линтер и тесты с проверкой порога покрытия.

---

<details>
<summary>Автоматические тесты Хекслета</summary>

Тесты запускаются на каждый коммит. За запуск отвечает файл `.github/workflows/hexlet-check.yml` — не удаляйте и не переименовывайте ни его, ни репозиторий.

</details>

## О Хекслете

[Хекслет](https://ru.hexlet.io/) — школа программирования: авторские программы обучения с практикой, поддержкой наставников и реальными проектами, которые остаются в резюме. Этот репозиторий — один из таких проектов.
