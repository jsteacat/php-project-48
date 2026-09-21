<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\NodeType;
use PHPUnit\Framework\TestCase;

use function Gendiff\buildDiff;

/**
 * Промежуточное представление diff: типы узлов и вложенные дети.
 * Форматирование здесь не проверяется — за него отвечает форматтер.
 */
class BuildDiffTest extends TestCase
{
    public function testScalarNodeTypes(): void
    {
        $diff = buildDiff(
            ['same' => 1, 'old' => 2, 'gone' => 3],
            ['same' => 1, 'old' => 20, 'fresh' => 4]
        );

        $this->assertSame(
            [
                // Ключи всегда отсортированы.
                'fresh' => ['type' => NodeType::Added, 'value' => 4],
                'gone' => ['type' => NodeType::Removed, 'value' => 3],
                'old' => ['type' => NodeType::Changed, 'oldValue' => 2, 'newValue' => 20],
                'same' => ['type' => NodeType::Unchanged, 'value' => 1],
            ],
            $diff
        );
    }

    public function testNestedNodeHasChildren(): void
    {
        $diff = buildDiff(
            ['group' => ['kept' => 1, 'changed' => 2]],
            ['group' => ['kept' => 1, 'changed' => 3, 'new' => 4]]
        );

        $this->assertSame(
            [
                'group' => [
                    'type' => NodeType::Nested,
                    'children' => [
                        'changed' => ['type' => NodeType::Changed, 'oldValue' => 2, 'newValue' => 3],
                        'kept' => ['type' => NodeType::Unchanged, 'value' => 1],
                        'new' => ['type' => NodeType::Added, 'value' => 4],
                    ],
                ],
            ],
            $diff
        );
    }

    public function testArrayAgainstScalarIsChangedValue(): void
    {
        $diff = buildDiff(['nest' => ['key' => 'value']], ['nest' => 'str']);

        $this->assertSame(
            ['nest' => ['type' => NodeType::Changed, 'oldValue' => ['key' => 'value'], 'newValue' => 'str']],
            $diff
        );
    }
}
