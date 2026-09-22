<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Formatters\PlainFormatter;
use Gendiff\NodeType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Плоский формат проверяется отдельно от парсинга:
 * на вход подаётся готовое промежуточное представление diff.
 */
class PlainFormatterTest extends TestCase
{
    /**
     * @param array<int|string, array<string, mixed>> $diff
     */
    private function formatDiff(array $diff): string
    {
        return (new PlainFormatter())->format($diff);
    }

    public function testSkipsUnchangedAndNestedWithoutChanges(): void
    {
        $diff = [
            'host' => ['type' => NodeType::Unchanged, 'value' => 'hexlet.io'],
            'group' => [
                'type' => NodeType::Nested,
                'children' => [
                    'kept' => ['type' => NodeType::Unchanged, 'value' => 1],
                ],
            ],
        ];

        $this->assertSame('', $this->formatDiff($diff));
    }

    public function testEmptyDiff(): void
    {
        $this->assertSame('', $this->formatDiff([]));
    }

    public function testAddedRemovedChanged(): void
    {
        $diff = [
            'follow' => ['type' => NodeType::Added, 'value' => false],
            'proxy' => ['type' => NodeType::Removed, 'value' => '123.234.53.22'],
            'timeout' => ['type' => NodeType::Changed, 'oldValue' => 50, 'newValue' => 20],
            'verbose' => ['type' => NodeType::Added, 'value' => true],
        ];

        $expected = <<<OUTPUT
        Property 'follow' was added with value: false
        Property 'proxy' was removed
        Property 'timeout' was updated. From 50 to 20
        Property 'verbose' was added with value: true
        OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    #[DataProvider('scalarProvider')]
    public function testRenderScalarValues(mixed $value, string $expected): void
    {
        $diff = ['key' => ['type' => NodeType::Added, 'value' => $value]];

        $this->assertSame("Property 'key' was added with value: {$expected}", $this->formatDiff($diff));
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
            'string' => ['hello', "'hello'"],
            'empty string' => ['', "''"],
            'array' => [['key' => 'value'], '[complex value]'],
            'list' => [[1, 2], '[complex value]'],
        ];
    }

    public function testChangedWithComplexValues(): void
    {
        $diff = [
            'nest' => [
                'type' => NodeType::Changed,
                'oldValue' => ['key' => 'value'],
                'newValue' => 'str',
            ],
        ];

        $expected = "Property 'nest' was updated. From [complex value] to 'str'";

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    /**
     * Вложенные узлы раскрываются полным путём от корня через точку.
     */
    public function testNestedPath(): void
    {
        $diff = [
            'common' => [
                'type' => NodeType::Nested,
                'children' => [
                    'setting6' => [
                        'type' => NodeType::Nested,
                        'children' => [
                            'doge' => [
                                'type' => NodeType::Nested,
                                'children' => [
                                    'wow' => [
                                        'type' => NodeType::Changed,
                                        'oldValue' => '',
                                        'newValue' => 'so much',
                                    ],
                                ],
                            ],
                            'ops' => ['type' => NodeType::Added, 'value' => 'vops'],
                        ],
                    ],
                    'setting2' => ['type' => NodeType::Removed, 'value' => 200],
                ],
            ],
            'group2' => [
                'type' => NodeType::Removed,
                'value' => ['abc' => 12345],
            ],
        ];

        $expected = <<<OUTPUT
        Property 'common.setting6.doge.wow' was updated. From '' to 'so much'
        Property 'common.setting6.ops' was added with value: 'vops'
        Property 'common.setting2' was removed
        Property 'group2' was removed
        OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    public function testUnknownNodeType(): void
    {
        $this->expectException(\UnhandledMatchError::class);

        $this->formatDiff(['key' => ['type' => 'unknown', 'value' => 1]]);
    }
}
