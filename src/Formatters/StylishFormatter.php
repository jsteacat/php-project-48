<?php

declare(strict_types=1);

namespace Gendiff\Formatters;

use Gendiff\NodeType;

class StylishFormatter implements Formatter
{
    private const int SPACES_PER_LEVEL = 4;
    private const int MARKER_SHIFT = 2;

    /**
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
     *     children?: array<int|string, mixed>}> $diff
     */
    public function format(array $diff): string
    {
        if ($diff === []) {
            return "{\n}";
        }

        return "{\n" . $this->renderChildren($diff, 1) . "\n}";
    }

    /**
     * Рисует детей узла на заданной глубине: отступ растёт на 4 пробела
     * за уровень, маркер (+/-/пробел) сдвигается влево на 2.
     *
     * @param array<int|string, mixed> $diff
     */
    private function renderChildren(array $diff, int $depth): string
    {
        $lines = [];

        foreach ($diff as $key => $node) {
            /** @var array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
             *     children?: array<int|string, mixed>} $node
             */
            $lines = [...$lines, ...$this->renderNode((string) $key, $node, $depth)];
        }

        return implode("\n", $lines);
    }

    /**
     * @param array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
     *     children?: array<int|string, mixed>} $node
     *
     * @return string[]
     */
    private function renderNode(string $key, array $node, int $depth): array
    {
        return match ($node['type']) {
            NodeType::Unchanged => [$this->renderLine($key, $node['value'] ?? null, $depth)],
            NodeType::Nested => [
                $this->indent($depth) . "{$key}: {",
                $this->renderChildren($node['children'] ?? [], $depth + 1),
                $this->closingIndent($depth) . '}',
            ],
            NodeType::Changed => [
                $this->renderLine($key, $node['oldValue'] ?? null, $depth, '-'),
                $this->renderLine($key, $node['newValue'] ?? null, $depth, '+'),
            ],
            NodeType::Removed => [$this->renderLine($key, $node['value'] ?? null, $depth, '-')],
            NodeType::Added => [$this->renderLine($key, $node['value'] ?? null, $depth, '+')],
        };
    }

    private function renderLine(string $key, mixed $value, int $depth, string $marker = ' '): string
    {
        return $this->indent($depth, $marker) . "{$key}: " . $this->renderValue($value, $depth);
    }

    /**
     * Отступ строки: глубина * 4 пробела минус сдвиг маркера влево.
     */
    private function indent(int $depth, string $marker = ' '): string
    {
        return str_repeat(' ', $depth * self::SPACES_PER_LEVEL - self::MARKER_SHIFT) . "{$marker} ";
    }

    /**
     * Отступ закрывающей скобки блока: у детей нет маркеров и сдвига влево.
     */
    private function closingIndent(int $depth): string
    {
        return str_repeat(' ', $depth * self::SPACES_PER_LEVEL);
    }

    /**
     * Приводит значение к виду, принятому в выводе. Массив рисуется блоком:
     * внутренние ключи — без маркеров, вложенные массивы — рекурсивно.
     */
    private function renderValue(mixed $value, int $depth): string
    {
        if (is_array($value)) {
            $lines = ['{'];

            foreach ($value as $key => $item) {
                $lines[] = $this->closingIndent($depth + 1) . "{$key}: " . $this->renderValue($item, $depth + 1);
            }

            $lines[] = $this->closingIndent($depth) . '}';

            return implode("\n", $lines);
        }

        return match (true) {
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            default => (string) $value,
        };
    }
}
