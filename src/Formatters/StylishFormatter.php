<?php

namespace Gendiff\Formatters;

use Gendiff\NodeType;

class StylishFormatter implements Formatter
{
    /**
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed}> $diff
     */
    public function format(array $diff): string
    {
        $lines = ['{'];

        foreach ($diff as $key => $node) {
            $lines = [...$lines, ...$this->renderNode((string) $key, $node)];
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * @param array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed} $node
     *
     * @return string[]
     */
    private function renderNode(string $key, array $node): array
    {
        return match ($node['type']) {
            NodeType::Unchanged => [$this->renderLine('', $key, $node['value'] ?? null)],
            NodeType::Changed => [
                $this->renderLine('- ', $key, $node['oldValue'] ?? null),
                $this->renderLine('+ ', $key, $node['newValue'] ?? null),
            ],
            NodeType::Removed => [$this->renderLine('- ', $key, $node['value'] ?? null)],
            NodeType::Added => [$this->renderLine('+ ', $key, $node['value'] ?? null)],
        };
    }

    private function renderLine(string $marker, string $key, mixed $value): string
    {
        return "  {$marker}{$key}: " . $this->renderValue($value);
    }

    /**
     * Приводит скаляр к виду, принятому в выводе.
     */
    private function renderValue(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            is_array($value) => throw new \InvalidArgumentException(
                'Массивы не поддерживаются: используйте рекурсивное сравнение'
            ),
            default => (string) $value,
        };
    }
}
