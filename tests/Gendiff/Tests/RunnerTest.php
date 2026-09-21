<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use Gendiff\Runner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RunnerTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures';
    private const EXPECTED_DIFF = self::FIXTURES_DIR . '/expected/stylish.txt';

    /**
     * Запускает Runner в текущем процессе, подменяя потоки вывода на in-memory.
     *
     * @param string[] $args
     *
     * @return array{0: string, 1: string, 2: int} stdout, stderr, код возврата
     */
    private function execute(array $args): array
    {
        $stdout = fopen('php://memory', 'w+');
        $stderr = fopen('php://memory', 'w+');

        $this->assertIsResource($stdout);
        $this->assertIsResource($stderr);

        $exitCode = Runner::run(array_merge(['gendiff'], $args), $stdout, $stderr);

        return [$this->readStream($stdout), $this->readStream($stderr), $exitCode];
    }

    /**
     * @param resource $stream
     */
    private function readStream($stream): string
    {
        rewind($stream);

        return (string) stream_get_contents($stream);
    }

    private function expectedDiff(): string
    {
        return (string) file_get_contents(self::EXPECTED_DIFF);
    }

    #[DataProvider('nestedFilesProvider')]
    public function testDiffOfNestedFiles(string $first, string $second): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute([
            self::FIXTURES_DIR . '/' . $first,
            self::FIXTURES_DIR . '/' . $second,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
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

    /**
     * @param string[] $option
     */
    #[DataProvider('formatOptionProvider')]
    public function testFormatOption(array $option): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute(array_merge($option, [
            self::FIXTURES_DIR . '/file1.json',
            self::FIXTURES_DIR . '/file2.json',
        ]));

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
    }

    /**
     * @return array<string, array{0: string[]}>
     */
    public static function formatOptionProvider(): array
    {
        return [
            'short' => [['-f', 'stylish']],
            'long' => [['--format', 'stylish']],
        ];
    }

    public function testUnknownOutputFormat(): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute([
            '-f',
            'unknown',
            self::FIXTURES_DIR . '/file1.json',
            self::FIXTURES_DIR . '/file2.json',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Неподдерживаемый формат вывода: unknown', $stderr);
    }

    public function testHelp(): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute(['--help']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Generate diff', $stdout);
        $this->assertStringContainsString('Usage:', $stdout);
        $this->assertSame('', $stderr);
    }

    public function testVersion(): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute(['--version']);

        $this->assertSame(0, $exitCode);
        $this->assertSame('1.0.0', trim($stdout));
        $this->assertSame('', $stderr);
    }

    public function testMissingArguments(): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Usage:', $stderr);
    }

    #[DataProvider('brokenFilesProvider')]
    public function testBrokenFiles(string $fileName, string $expectedError): void
    {
        $path = self::FIXTURES_DIR . '/' . $fileName;

        [$stdout, $stderr, $exitCode] = $this->execute([$path, $path]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString($expectedError, $stderr);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function brokenFilesProvider(): array
    {
        return [
            'unsupported format' => ['unsupported.txt', 'Неподдерживаемый формат файла'],
            'invalid json' => ['invalid.json', 'Ошибка JSON в файле'],
            'invalid yaml' => ['invalid.yaml', 'Ошибка YAML в файле'],
            'empty file' => ['empty.json', 'Пустой файл'],
            'missing file' => ['missing.json', 'Файл не найден или недоступен'],
        ];
    }
}
