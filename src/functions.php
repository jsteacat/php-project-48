<?php

namespace Gendiff;

use Gendiff\Exceptions\UnsupportedFormatException;
use Gendiff\Formatters\Formatter;
use Gendiff\Formatters\StylishFormatter;

/**
 * Сравнивает два файла и возвращает их различия в выбранном формате вывода.
 */
function genDiff(
    string $firstFilePath,
    string $secondFilePath,
    string $format = Constants::DEFAULT_FORMAT
): string {
    $firstData = Parser::parse($firstFilePath);
    $secondData = Parser::parse($secondFilePath);

    return createFormatter($format)->format(buildDiff($firstData, $secondData));
}

/**
 * Собирает различия в промежуточное представление: ключ => узел.
 * Формат вывода на этом шаге не важен: узлы получает форматтер.
 *
 * @param array<int|string, mixed> $first
 * @param array<int|string, mixed> $second
 *
 * @return array<int|string, array<string, mixed>>
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
            $diff[$key] = ['type' => Constants::NODE_UNCHANGED, 'value' => $first[$key]];
        } elseif ($inFirst && $inSecond) {
            $diff[$key] = [
                'type' => Constants::NODE_CHANGED,
                'oldValue' => $first[$key],
                'newValue' => $second[$key],
            ];
        } elseif ($inFirst) {
            $diff[$key] = ['type' => Constants::NODE_REMOVED, 'value' => $first[$key]];
        } else {
            $diff[$key] = ['type' => Constants::NODE_ADDED, 'value' => $second[$key]];
        }
    }

    return $diff;
}

/**
 * Выбирает форматтер вывода: новые форматы (plain, json) добавляются здесь.
 */
function createFormatter(string $format): Formatter
{
    return match ($format) {
        Constants::FORMAT_STYLISH => new StylishFormatter(),
        default => throw new UnsupportedFormatException("Неподдерживаемый формат вывода: {$format}"),
    };
}
