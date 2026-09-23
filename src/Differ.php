<?php

declare(strict_types=1);

namespace Differ\Differ;

/**
 * Совместимость с тестами Хекслета: прокси к публичному API.
 *
 * Вызов идёт через бекслеш осознанно: `use function Gendiff\genDiff` в этом же файле
 * конфликтует с локальной genDiff() — PHP падает с "Cannot redeclare function".
 */
function genDiff(
    string $firstFilePath,
    string $secondFilePath,
    string $format = 'stylish'
): string {
    return \Gendiff\genDiff($firstFilePath, $secondFilePath, $format);
}
