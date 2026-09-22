<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Exceptions\UnsupportedFormatException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Gendiff\genDiff;

/**
 * Тесты публичного API библиотеки: функция genDiff сравнивает вложенные файлы.
 * Тесты на вложенных структурах полностью покрывают плоские,
 * поэтому плоские файлы здесь отдельно не проверяются.
 */
class GenDiffTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures';

    #[DataProvider('nestedFilesProvider')]
    public function testComparisonOfNestedFiles(string $first, string $second): void
    {
        $this->assertSame($this->expectedStylish(), genDiff(
            self::FIXTURES_DIR . '/' . $first,
            self::FIXTURES_DIR . '/' . $second
        ));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function nestedFilesProvider(): array
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

    #[DataProvider('nestedFilesProvider')]
    public function testPlainFormat(string $first, string $second): void
    {
        $this->assertSame($this->expectedPlain(), genDiff(
            self::FIXTURES_DIR . '/' . $first,
            self::FIXTURES_DIR . '/' . $second,
            'plain'
        ));
    }

    public function testUnknownOutputFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Неподдерживаемый формат вывода: unknown');

        genDiff(self::FIXTURES_DIR . '/file1.json', self::FIXTURES_DIR . '/file2.json', 'unknown');
    }

    /**
     * Ожидаемый вывод хранится в фикстуре, чтобы не дублировать его в тестах.
     */
    private function expectedStylish(): string
    {
        return rtrim((string) file_get_contents(self::FIXTURES_DIR . '/expected/stylish.txt'));
    }

    private function expectedPlain(): string
    {
        return rtrim((string) file_get_contents(self::FIXTURES_DIR . '/expected/plain.txt'));
    }
}
