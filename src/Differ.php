<?php

namespace Gendiff;

function stringify(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? 'true' : 'false',
        is_null($value) => 'null',
        default => (string) $value,
    };
}

function genDiff(string $firstFilePath, string $secondFilePath): string
{
    $firstData = Parser::parse($firstFilePath);
    $secondData = Parser::parse($secondFilePath);

    $keys = array_unique(array_merge(array_keys($firstData), array_keys($secondData)));
    $sortedKeys = $keys;
    sort($sortedKeys);

    $lines = ['{'];

    foreach ($sortedKeys as $key) {
        $inFirst = array_key_exists($key, $firstData);
        $inSecond = array_key_exists($key, $secondData);

        if ($inFirst && $inSecond) {
            if ($firstData[$key] === $secondData[$key]) {
                $lines[] = "  {$key}: " . stringify($firstData[$key]);
            } else {
                $lines[] = "  - {$key}: " . stringify($firstData[$key]);
                $lines[] = "  + {$key}: " . stringify($secondData[$key]);
            }
        } elseif ($inFirst) {
            $lines[] = "  - {$key}: " . stringify($firstData[$key]);
        } else {
            $lines[] = "  + {$key}: " . stringify($secondData[$key]);
        }
    }

    $lines[] = '}';

    return implode("\n", $lines);
}
