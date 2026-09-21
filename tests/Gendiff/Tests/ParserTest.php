<?php

declare(strict_types=1);

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
        $first = [
            'common' => [
                'setting1' => 'Value 1',
                'setting2' => 200,
                'setting3' => true,
                'setting6' => ['key' => 'value', 'doge' => ['wow' => '']],
            ],
            'group1' => ['baz' => 'bas', 'foo' => 'bar', 'nest' => ['key' => 'value']],
            'group2' => ['abc' => 12345, 'deep' => ['id' => 45]],
        ];

        $second = [
            'common' => [
                'follow' => false,
                'setting1' => 'Value 1',
                'setting3' => null,
                'setting4' => 'blah blah',
                'setting5' => ['key5' => 'value5'],
                'setting6' => ['key' => 'value', 'ops' => 'vops', 'doge' => ['wow' => 'so much']],
            ],
            'group1' => ['foo' => 'bar', 'baz' => 'bars', 'nest' => 'str'],
            'group3' => ['deep' => ['id' => ['number' => 45]], 'fee' => 100500],
        ];

        return [
            'json' => ['file1.json', $first],
            'yaml' => ['file1.yaml', $first],
            'yml' => ['file2.yml', $second],
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
     * Сообщение об ошибке строится по списку расширений из FileFormat.
     */
    public function testUnsupportedFormatMessageListsSupportedExtensions(): void
    {
        $this->expectException(UnsupportedFileFormatException::class);
        $this->expectExceptionMessage('Поддерживаются: json, yaml, yml');

        Parser::parse(self::FIXTURES_DIR . '/unsupported.txt');
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
