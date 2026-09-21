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

    public function testArrayValueIsNotSupported(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Массивы не поддерживаются');

        $this->formatDiff(['key' => ['type' => NodeType::Added, 'value' => ['nested' => true]]]);
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
