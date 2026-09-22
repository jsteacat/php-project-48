<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Formatters\JsonFormatter;
use Gendiff\NodeType;
use PHPUnit\Framework\TestCase;

/**
 * JSON-формат проверяется отдельно от парсинга:
 * на вход подаётся готовое промежуточное представление diff.
 */
class JsonFormatterTest extends TestCase
{
    /**
     * @param array<int|string, array<string, mixed>> $diff
     */
    private function formatDiff(array $diff): string
    {
        return (new JsonFormatter())->format($diff);
    }

    public function testEmptyDiff(): void
    {
        $this->assertSame('[]', $this->formatDiff([]));
    }

    public function testAllNodeTypes(): void
    {
        $diff = [
            'follow' => ['type' => NodeType::Added, 'value' => false],
            'host' => ['type' => NodeType::Unchanged, 'value' => 'hexlet.io'],
            'proxy' => ['type' => NodeType::Removed, 'value' => '123.234.53.22'],
            'timeout' => ['type' => NodeType::Changed, 'oldValue' => 50, 'newValue' => 20],
        ];

        $expected = <<<OUTPUT
        {
            "follow": {
                "type": "added",
                "value": false
            },
            "host": {
                "type": "unchanged",
                "value": "hexlet.io"
            },
            "proxy": {
                "type": "removed",
                "value": "123.234.53.22"
            },
            "timeout": {
                "type": "changed",
                "oldValue": 50,
                "newValue": 20
            }
        }
        OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    public function testNestedDiff(): void
    {
        $diff = [
            'common' => [
                'type' => NodeType::Nested,
                'children' => [
                    'setting1' => ['type' => NodeType::Unchanged, 'value' => 'Value 1'],
                    'setting5' => [
                        'type' => NodeType::Added,
                        'value' => ['key5' => 'value5'],
                    ],
                ],
            ],
        ];

        $expected = <<<OUTPUT
        {
            "common": {
                "type": "nested",
                "children": {
                    "setting1": {
                        "type": "unchanged",
                        "value": "Value 1"
                    },
                    "setting5": {
                        "type": "added",
                        "value": {
                            "key5": "value5"
                        }
                    }
                }
            }
        }
        OUTPUT;

        $this->assertSame($expected, $this->formatDiff($diff));
    }

    public function testOutputIsValidJson(): void
    {
        $diff = [
            'key' => ['type' => NodeType::Added, 'value' => null],
        ];

        $decoded = json_decode($this->formatDiff($diff), true);

        $this->assertSame(['key' => ['type' => 'added', 'value' => null]], $decoded);
    }
}
