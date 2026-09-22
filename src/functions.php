<?php

declare(strict_types=1);

namespace Gendiff;

use Gendiff\Formatters\Formatter;
use Gendiff\Formatters\JsonFormatter;
use Gendiff\Formatters\PlainFormatter;
use Gendiff\Formatters\StylishFormatter;

/**
 * Сравнивает два файла и возвращает их различия в выбранном формате вывода.
 *
 * Формат приходит строкой — так его передают из CLI и по спецификации API —
 * и сразу приводится к enum'у OutputFormat.
 */
function genDiff(
    string $firstFilePath,
    string $secondFilePath,
    string $format = OutputFormat::Stylish->value
): string {
    $outputFormat = OutputFormat::fromString($format);

    $firstData = Parser::parse($firstFilePath);
    $secondData = Parser::parse($secondFilePath);

    return createFormatter($outputFormat)->format(buildDiff($firstData, $secondData));
}

/**
 * Собирает различия в промежуточное представление: ключ => узел.
 * Формат вывода на этом шаге не важен: узлы получает форматтер.
 *
 * Если значения по ключу в обоих массивах — ассоциативные массивы,
 * ключ превращается во вложенный узел (Nested) с детьми, построенными
 * рекурсивно. Иначе значения никак не анализируются и хранятся как есть:
 * их форматирование — ответственность форматтера.
 *
 * @param array<int|string, mixed> $first
 * @param array<int|string, mixed> $second
 *
 * @return array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
 *     children?: array<int|string, mixed>}>
 */
function buildDiff(array $first, array $second): array
{
    /** @var array<int, int|string> $keys */
    $keys = array_values(array_unique(array_merge(array_keys($first), array_keys($second))));
    sort($keys);

    $diff = [];

    foreach ($keys as $key) {
        $inFirst = array_key_exists($key, $first);
        $inSecond = array_key_exists($key, $second);

        if ($inFirst && $inSecond) {
            $firstValue = $first[$key];
            $secondValue = $second[$key];

            if (isAssocArray($firstValue) && isAssocArray($secondValue)) {
                $diff[$key] = ['type' => NodeType::Nested, 'children' => buildDiff($firstValue, $secondValue)];
            } elseif ($firstValue === $secondValue) {
                $diff[$key] = ['type' => NodeType::Unchanged, 'value' => $firstValue];
            } else {
                $diff[$key] = [
                    'type' => NodeType::Changed,
                    'oldValue' => $firstValue,
                    'newValue' => $secondValue,
                ];
            }
        } elseif ($inFirst) {
            $diff[$key] = ['type' => NodeType::Removed, 'value' => $first[$key]];
        } else {
            $diff[$key] = ['type' => NodeType::Added, 'value' => $second[$key]];
        }
    }

    return $diff;
}

/**
 * Проверяет, что значение — ассоциативный массив (объект в JSON/YAML),
 * а не обычный список: дети строятся только в этом случае.
 */
function isAssocArray(mixed $value): bool
{
    return is_array($value) && !array_is_list($value);
}

/**
 * Выбирает форматтер вывода: новые форматы добавляются здесь.
 */
function createFormatter(OutputFormat $format): Formatter
{
    return match ($format) {
        OutputFormat::Stylish => new StylishFormatter(),
        OutputFormat::Plain => new PlainFormatter(),
        OutputFormat::Json => new JsonFormatter(),
    };
}
