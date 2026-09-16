<?php

namespace Gendiff\Tests;

use Gendiff\Exceptions\UnsupportedFileFormatException;
use Gendiff\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures';

    /**
     * @param array<mixed> $expected
     */
    #[DataProvider('validFilesProvider')]
    public function testParseFile(string $fileName, array $expected): void
    {
        $this->assertSame($expected, Parser::parse(self::FIXTURES_DIR . '/' . $fileName));
    }

    /**
     * @return array<string, array{0: string, 1: array<mixed>}>
     */
    public static function validFilesProvider(): array
    {
        $flat = ['host' => 'hexlet.io', 'timeout' => 50, 'proxy' => '123.234.53.22', 'follow' => false];

        return [
            'json' => ['file1.json', $flat],
            'yaml' => ['file1.yaml', $flat],
            'yml' => ['file2.yml', ['timeout' => 20, 'verbose' => true, 'host' => 'hexlet.io']],
            'nested json' => [
                'nested.json',
                ['person' => ['name' => 'Alice', 'address' => ['city' => 'Moscow', 'zip' => '123456']]],
            ],
            'value types' => [
                'values.json',
                [
                    'int' => 42,
                    'float' => 3.14,
                    'negative' => -10,
                    'enabled' => true,
                    'disabled' => false,
                    'nothing' => null,
                ],
            ],
            'root array' => ['list.json', [1, 2, 3]],
        ];
    }

    /**
     * @param class-string<\Throwable> $exceptionClass
     */
    #[DataProvider('brokenFilesProvider')]
    public function testParseBrokenFile(string $fileName, string $exceptionClass, string $message): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessageMatches($message);

        Parser::parse(self::FIXTURES_DIR . '/' . $fileName);
    }

    /**
     * @return array<string, array{0: string, 1: class-string<\Throwable>, 2: string}>
     */
    public static function brokenFilesProvider(): array
    {
        return [
            'missing file' => ['missing.json', \InvalidArgumentException::class, '/Файл не найден или недоступен:/'],
            'empty file' => ['empty.json', \InvalidArgumentException::class, '/Пустой файл:/'],
            'invalid json' => ['invalid.json', \InvalidArgumentException::class, '/Ошибка JSON в файле/'],
            'invalid yaml' => ['invalid.yaml', \InvalidArgumentException::class, '/Ошибка YAML в файле/'],
            'json scalar' => ['scalar.json', \InvalidArgumentException::class, '/Ожидается объект или массив JSON/'],
            'yaml scalar' => ['scalar.yaml', \InvalidArgumentException::class, '/Ожидается объект или массив YAML/'],
            'unsupported format' => [
                'unsupported.txt',
                UnsupportedFileFormatException::class,
                '/Неподдерживаемый формат файла:/',
            ],
        ];
    }
}
