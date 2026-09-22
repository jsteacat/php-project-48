<?php

declare(strict_types=1);

namespace Gendiff\Formatters;

use Gendiff\NodeType;

class PlainFormatter implements Formatter
{
    /**
     * Собирает diff в плоский список строк: только изменения,
     * вложенные ключи — полным путём от корня через точку.
     *
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
     *     children?: array<int|string, mixed>}> $diff
     */
    public function format(array $diff): string
    {
        return implode("\n", $this->renderNodes($diff, ''));
    }

    /**
     * @param array<int|string, mixed> $diff
     *
     * @return string[]
     */
    private function renderNodes(array $diff, string $parentPath): array
    {
        $lines = [];

        foreach ($diff as $key => $node) {
            /** @var array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
             *     children?: array<int|string, mixed>} $node
             */
            $path = $parentPath === '' ? (string) $key : "{$parentPath}.{$key}";

            $lines = match ($node['type']) {
                NodeType::Unchanged => $lines,
                NodeType::Nested => array_merge(
                    $lines,
                    $this->renderNodes($node['children'] ?? [], $path)
                ),
                NodeType::Added => [
                    ...$lines,
                    "Property '{$path}' was added with value: " . $this->renderValue($node['value'] ?? null),
                ],
                NodeType::Removed => [...$lines, "Property '{$path}' was removed"],
                NodeType::Changed => [
                    ...$lines,
                    "Property '{$path}' was updated. From "
                        . $this->renderValue($node['oldValue'] ?? null)
                        . ' to '
                        . $this->renderValue($node['newValue'] ?? null),
                ],
            };
        }

        return $lines;
    }

    /**
     * Приводит значение к виду plain: массивы — [complex value],
     * строки — в одинарных кавычках, остальное — как есть.
     */
    private function renderValue(mixed $value): string
    {
        if (is_array($value)) {
            return '[complex value]';
        }

        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            is_string($value) => "'{$value}'",
            default => (string) $value,
        };
    }
}
