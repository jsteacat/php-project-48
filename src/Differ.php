<?php

declare(strict_types=1);

namespace Differ\Differ;

/**
 * Совместимость с тестами Хекслета: прокси к публичному API.
 */
function genDiff(
    string $firstFilePath,
    string $secondFilePath,
    string $format = 'stylish'
): string {
    return \Gendiff\genDiff($firstFilePath, $secondFilePath, $format);
}
