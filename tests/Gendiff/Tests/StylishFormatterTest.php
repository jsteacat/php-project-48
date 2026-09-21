<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Formatters\StylishFormatter;
use Gendiff\NodeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Формат вывода тестируется отдельно от парсинга и разбора аргументов:
 * на вход подаётся готовое промежуточное представление diff.
 */
class StylishFormatterTest extends TestCase
{
    /**
     * @param array<int|string, array<string, mixed>> $diff
     */
    private function formatDiff(array $diff): string
    {
        return (new StylishFormatter())->format($diff);
    }

    public function testAllNodeTypes(): void
    {
        $diff = [
            'follow' => ['type' => NodeType::Removed, 'value' => false],
            'host' => ['type' => NodeType::Unchanged, 'value' => 'hexlet.io'],
            'proxy' => ['type' => NodeType::Removed, 'value' => '123.234.53.22'],
            'timeout' => ['type' => NodeType::Changed, 'oldValue' => 50, 'newValue' => 20],
            'verbose' => ['type' => NodeType::Added, 'value' => true],
        ];

        $expected = <<<OUTPUT
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    public function testEmptyDiff(): void
    {
        $this->assertSame("{\n}", $this->formatDiff([]));
    }

    #[DataProvider('scalarProvider')]
    public function testRenderScalarValues(mixed $value, string $expected): void
    {
        $diff = ['key' => ['type' => NodeType::Added, 'value' => $value]];

        $this->assertSame("{\n  + key: {$expected}\n}", $this->formatDiff($diff));
    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function scalarProvider(): array
    {
        return [
            'boolean true' => [true, 'true'],
            'boolean false' => [false, 'false'],
            'null' => [null, 'null'],
            'integer' => [42, '42'],
            'float' => [3.14, '3.14'],
            'string' => ['hello', 'hello'],
        ];
    }

    /**
     * Побеждает рекурсия: вложенный узел рисуется с отступом по глубине,
     * а значение-массив у добавленного ключа — блоком без маркеров внутри.
     */
    public function testNestedDiff(): void
    {
        $diff = [
            'common' => [
                'type' => NodeType::Nested,
                'children' => [
                    'setting1' => ['type' => NodeType::Unchanged, 'value' => 'Value 1'],
                    'setting2' => ['type' => NodeType::Removed, 'value' => 200],
                    'setting5' => [
                        'type' => NodeType::Added,
                        'value' => ['key5' => 'value5'],
                    ],
                ],
            ],
            'group2' => [
                'type' => NodeType::Removed,
                'value' => ['abc' => 12345, 'deep' => ['id' => 45]],
            ],
        ];

        $expected = <<<'OUTPUT'
            {
                common: {
                    setting1: Value 1
                  - setting2: 200
                  + setting5: {
                        key5: value5
                    }
                }
              - group2: {
                    abc: 12345
                    deep: {
                        id: 45
                    }
                }
            }
            OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    public function testChangedNodeWithArrayValue(): void
    {
        $diff = [
            'nest' => [
                'type' => NodeType::Changed,
                'oldValue' => ['key' => 'value'],
                'newValue' => 'str',
            ],
        ];

        $expected = <<<'OUTPUT'
            {
              - nest: {
                    key: value
                }
              + nest: str
            }
            OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    /**
     * Все case'ы NodeType разобраны в match, поэтому неизвестный тип узла —
     * это уже нарушение контракта, и PHP бросает UnhandledMatchError.
     */
    public function testUnknownNodeType(): void
    {
        $this->expectException(\UnhandledMatchError::class);

        $this->formatDiff(['key' => ['type' => 'unknown', 'value' => 1]]);
    }
}
