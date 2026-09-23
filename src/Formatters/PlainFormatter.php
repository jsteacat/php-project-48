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
                NodeType::Nested => [...$lines, ...$this->renderNodes($node['children'] ?? [], $path)],
                NodeType::Added => [...$lines, $this->renderAdded($path, $node['value'] ?? null)],
                NodeType::Removed => [...$lines, $this->renderRemoved($path)],
                NodeType::Changed => [
                    ...$lines,
                    $this->renderChanged($path, $node['oldValue'] ?? null, $node['newValue'] ?? null),
                ],
            };
        }

        return $lines;
    }

    private function renderAdded(string $path, mixed $value): string
    {
        return "Property '{$path}' was added with value: " . $this->renderValue($value);
    }

    private function renderRemoved(string $path): string
    {
        return "Property '{$path}' was removed";
    }

    private function renderChanged(string $path, mixed $oldValue, mixed $newValue): string
    {
        return "Property '{$path}' was updated. From "
            . $this->renderValue($oldValue)
            . ' to '
            . $this->renderValue($newValue);
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
