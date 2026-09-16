<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;
use Gendiff\Runner;

class RunnerTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/gendiff_runner_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }

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

    /**
     * @return string[] пути к созданным файлам
     */
    private function createJsonFixtures(string $first, string $second): array
    {
        file_put_contents($this->tempDir . '/file1.json', $first);
        file_put_contents($this->tempDir . '/file2.json', $second);

        return [$this->tempDir . '/file1.json', $this->tempDir . '/file2.json'];
    }

    public function testValidFiles(): void
    {
        $files = $this->createJsonFixtures('{"a": 1, "b": 2}', '{"a": 1, "b": 3}');

        [$stdout, $stderr, $exitCode] = $this->execute($files);

        $expected = <<<OUTPUT
{
  a: 1
  - b: 2
  + b: 3
}

OUTPUT;

        $this->assertSame(0, $exitCode);
        $this->assertSame($expected, $stdout);
        $this->assertSame('', $stderr);
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
        $this->assertStringContainsString('Usage:', $stderr);
    }

    public function testFormatShortOption(): void
    {
        $files = $this->createJsonFixtures('{"a": 1}', '{"a": 2}');

        [$stdout, $stderr, $exitCode] = $this->execute(array_merge(['-f', 'stylish'], $files));

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('- a: 1', $stdout);
        $this->assertStringContainsString('+ a: 2', $stdout);
        $this->assertSame('', $stderr);
    }

    public function testFormatLongOption(): void
    {
        $files = $this->createJsonFixtures('{"a": 1}', '{"a": 2}');

        [$stdout, $stderr, $exitCode] = $this->execute(array_merge(['--format', 'stylish'], $files));

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('- a: 1', $stdout);
        $this->assertStringContainsString('+ a: 2', $stdout);
        $this->assertSame('', $stderr);
    }

    public function testNonExistentFile(): void
    {
        [$stdout, $stderr, $exitCode] = $this->execute(['/nonexistent/file1.json', '/nonexistent/file2.json']);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Файл не найден или недоступен', $stderr);
    }

    public function testUnsupportedFormat(): void
    {
        file_put_contents($this->tempDir . '/file.txt', 'some content');

        [$stdout, $stderr, $exitCode] = $this->execute([
            $this->tempDir . '/file.txt',
            $this->tempDir . '/file.txt',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Неподдерживаемый формат файла', $stderr);
    }

    public function testInvalidJson(): void
    {
        $files = $this->createJsonFixtures('{invalid json}', '{"a": 1}');

        [$stdout, $stderr, $exitCode] = $this->execute($files);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Ошибка JSON в файле', $stderr);
    }
}