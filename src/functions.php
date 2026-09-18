<?php

namespace Gendiff;

use Gendiff\Formatters\Formatter;
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
 * @param array<int|string, mixed> $first
 * @param array<int|string, mixed> $second
 *
 * @return array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed}>
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

        if ($inFirst && $inSecond && $first[$key] === $second[$key]) {
            $diff[$key] = ['type' => NodeType::Unchanged, 'value' => $first[$key]];
        } elseif ($inFirst && $inSecond) {
            $diff[$key] = [
                'type' => NodeType::Changed,
                'oldValue' => $first[$key],
                'newValue' => $second[$key],
            ];
        } elseif ($inFirst) {
            $diff[$key] = ['type' => NodeType::Removed, 'value' => $first[$key]];
        } else {
            $diff[$key] = ['type' => NodeType::Added, 'value' => $second[$key]];
        }
    }

    return $diff;
}

/**
 * Выбирает форматтер вывода: новые форматы (plain, json) добавляются здесь.
 */
function createFormatter(OutputFormat $format): Formatter
{
    return match ($format) {
        OutputFormat::Stylish => new StylishFormatter(),
    };
}
