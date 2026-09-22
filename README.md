# Вычислитель отличий (PHP)

[![hexlet-check](https://github.com/jsteacat/php-project-48/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/jsteacat/php-project-48/actions)
[![CI](https://github.com/jsteacat/php-project-48/actions/workflows/ci.yml/badge.svg)](https://github.com/jsteacat/php-project-48/actions/workflows/ci.yml)

В этом проекте отрабатывается работа с коллекциями. Изучаются способы построения и обхода деревьев. Вы познакомитесь с разными форматами данных (json, yml), научитесь их парсить и формировать. Начнете писать тесты (PHPUnit) и освоите разработку через них. Познакомитесь с непрерывной интеграцией (CI) и элементами экстремального программирования (XP). Прокачаете ООП мышление.

Учебный проект Хекслета: https://ru.hexlet.io/programs/php
Как это должно работать: https://asciinema.org/a/Pe6QypnLEmFWssNAjCOJN1iii

Пример работы пакета:

[![asciicast](https://asciinema.org/a/Pe6QypnLEmFWssNAjCOJN1iii.svg)](https://asciinema.org/a/Pe6QypnLEmFWssNAjCOJN1iii)

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

Поддерживаются файлы в форматах `.json`, `.yaml` и `.yml`. Форматы вывода: `stylish` (по умолчанию), `plain` и `json`: неизвестный формат — это ошибка с кодом возврата `1`.

```bash
# Плоский формат
./bin/gendiff --format plain tests/fixtures/file1.json tests/fixtures/file2.json

# JSON-представление diff
./bin/gendiff --format json tests/fixtures/file1.json tests/fixtures/file2.json
```

Пример вывода в формате `plain`:

```
Property 'common.follow' was added with value: false
Property 'common.setting2' was removed
Property 'common.setting3' was updated. From true to null
Property 'common.setting4' was added with value: 'blah blah'
Property 'common.setting5' was added with value: [complex value]
Property 'common.setting6.doge.wow' was updated. From '' to 'so much'
Property 'common.setting6.ops' was added with value: 'vops'
Property 'group1.baz' was updated. From 'bas' to 'bars'
Property 'group1.nest' was updated. From [complex value] to 'str'
Property 'group2' was removed
Property 'group3' was added with value: [complex value]
```

В `plain` выводятся только изменения: без изменений строка не печатается,
вложенные ключи — полным путём от корня через точку, составные значения —
как `[complex value]`, строковые — в одинарных кавычках, числа, `true`,
`false` и `null` — как есть.

Пример вывода в формате `json` (промежуточное представление diff):

```json
{
    "common": {
        "type": "nested",
        "children": {
            "follow": {
                "type": "added",
                "value": false
            },
            "setting3": {
                "type": "changed",
                "oldValue": true,
                "newValue": null
            }
        }
    },
    "group2": {
        "type": "removed",
        "value": {
            "abc": 12345
        }
    }
}
```

Полный вывод для вложенных фикстур — в `tests/fixtures/expected/json.txt`.

Пример вывода в формате `stylish`:

```
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            doge: {
              - wow: 
              + wow: so much
            }
            key: value
          + ops: vops
        }
    }
    group1: {
      - baz: bas
      + baz: bars
        foo: bar
      - nest: {
            key: value
        }
      + nest: str
    }
  - group2: {
        abc: 12345
        deep: {
            id: 45
        }
    }
  + group3: {
        deep: {
            id: {
                number: 45
            }
        }
        fee: 100500
    }
}
```

Вложенные объекты сравниваются рекурсивно: если значения по ключу — объекты
в обоих файлах, их различия разбираются по детям; иначе значения выводятся
как есть, а значение-объект у добавленного/удалённого/изменённого ключа
рисуется блоком без маркеров внутри.

Если файл не найден, его формат не поддерживается или содержимое повреждено, сообщение об ошибке выводится в поток ошибок (`stderr`), а код возврата процесса равен `1`.

### Как библиотека

```php
use function Gendiff\genDiff;

// третий аргумент — формат вывода, по умолчанию stylish
$diff = genDiff('path/to/file1.json', 'path/to/file2.json');
$diff = genDiff('path/to/file1.json', 'path/to/file2.json', 'stylish');
$diff = genDiff('path/to/file1.json', 'path/to/file2.json', 'plain');
$diff = genDiff('path/to/file1.json', 'path/to/file2.json', 'json');
echo $diff;
```

Внутри формат описан enum'ом `Gendiff\OutputFormat`: строка из аргументов приводится к enum'у
на входе, а неизвестный формат — исключение `UnsupportedFormatException`.

## Структура проекта

```
bin/gendiff           — CLI-входная точка
src/functions.php     — публичное API: genDiff(), сборка diff, выбор форматтера
src/NodeType.php      — типы узлов diff (enum)
src/OutputFormat.php  — форматы вывода (enum)
src/FileFormat.php    — форматы файлов на входе: json / yaml / yml (enum)
src/Parser.php        — чтение json / yaml / yml
src/Runner.php        — разбор аргументов и вывод результата
src/Formatters/       — форматы вывода: каждый вариант — отдельный класс
src/Exceptions/       — исключения проекта
tests/                — PHPUnit-тесты, tests/fixtures — фикстуры
```

Сравнение и вывод разделены: `buildDiff()` собирает различия в промежуточное представление,
а форматтер превращает их в текст. Закрытые наборы значений описаны enum'ами: `NodeType`
(типы узлов diff), `OutputFormat` (форматы вывода), `FileFormat` (форматы файлов на входе).
Форматы вывода: `stylish` — дерево с маркерами, `plain` — только изменения плоским списком,
`json` — JSON промежуточного представления.

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
