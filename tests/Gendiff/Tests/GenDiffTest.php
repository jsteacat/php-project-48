<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Exceptions\UnsupportedFormatException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Gendiff\genDiff;

/**
 * Тесты публичного API библиотеки: функция genDiff сравнивает плоские файлы.
 */
class GenDiffTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures';

    #[DataProvider('flatFilesProvider')]
    public function testComparisonOfFlatFiles(string $first, string $second): void
    {
        $this->assertSame($this->expectedStylish(), genDiff(
            self::FIXTURES_DIR . '/' . $first,
            self::FIXTURES_DIR . '/' . $second
        ));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function flatFilesProvider(): array
    {
        return [
            'json' => ['file1.json', 'file2.json'],
            'yaml' => ['file1.yaml', 'file2.yml'],
        ];
    }

    public function testExplicitStylishFormat(): void
    {
        $this->assertSame($this->expectedStylish(), genDiff(
            self::FIXTURES_DIR . '/file1.json',
            self::FIXTURES_DIR . '/file2.json',
            'stylish'
        ));
    }

    public function testUnknownOutputFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Неподдерживаемый формат вывода: plain');

        genDiff(self::FIXTURES_DIR . '/file1.json', self::FIXTURES_DIR . '/file2.json', 'plain');
    }

    /**
     * Ожидаемый вывод хранится в фикстуре, чтобы не дублировать его в тестах.
     */
    private function expectedStylish(): string
    {
        return rtrim((string) file_get_contents(self::FIXTURES_DIR . '/expected/stylish.txt'));
    }
}
