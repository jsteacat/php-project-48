<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

class CliTest extends TestCase
{
    private string $binPath;
    private string $fixturesDir;
    private string $tempDir;

    protected function setUp(): void
    {
        // __DIR__ = tests/Gendiff/Tests, поэтому до корня проекта три уровня вверх.
        $this->binPath = dirname(__DIR__, 3) . '/bin/gendiff';
        $this->fixturesDir = dirname(__DIR__, 2) . '/fixtures';
        $this->tempDir = sys_get_temp_dir() . '/gendiff_cli_test_' . uniqid();

        mkdir($this->tempDir, 0777, true);

        $this->assertFileExists($this->binPath);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }

    /**
     * Запускает настоящий процесс bin/gendiff и возвращает его потоки и код возврата.
     *
     * @param string[] $args
     *
     * @return array{0: string, 1: string, 2: int} stdout, stderr, код возврата
     */
    private function runCli(array $args): array
    {
        $process = proc_open(
            array_merge([PHP_BINARY, $this->binPath], $args),
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );

        $this->assertIsResource($process);

        $stdout = (string) stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        return [$stdout, $stderr, proc_close($process)];
    }

    private function expectedDiff(): string
    {
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

        return $expected . "\n";
    }

    public function testHelp(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli(['--help']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Generate diff', $stdout);
        $this->assertStringContainsString('Usage:', $stdout);
        $this->assertSame('', $stderr);
    }

    public function testVersion(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli(['--version']);

        $this->assertSame(0, $exitCode);
        $this->assertSame('1.0.0', trim($stdout));
        $this->assertSame('', $stderr);
    }

    public function testMissingArguments(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Usage:', $stderr);
    }

    public function testDiffOfJsonFixtures(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            $this->fixturesDir . '/file1.json',
            $this->fixturesDir . '/file2.json',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
    }

    public function testDiffOfYamlFixtures(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            $this->fixturesDir . '/file1.yaml',
            $this->fixturesDir . '/file2.yml',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
    }

    public function testShortFormatOption(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            '-f',
            'stylish',
            $this->fixturesDir . '/file1.json',
            $this->fixturesDir . '/file2.json',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
    }

    public function testLongFormatOption(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            '--format',
            'stylish',
            $this->fixturesDir . '/file1.json',
            $this->fixturesDir . '/file2.json',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertSame($this->expectedDiff(), $stdout);
        $this->assertSame('', $stderr);
    }

    public function testNonExistentFile(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli(['/nonexistent/file1.json', '/nonexistent/file2.json']);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Файл не найден или недоступен', $stderr);
    }

    public function testUnsupportedFormat(): void
    {
        file_put_contents($this->tempDir . '/file.txt', 'some content');

        [$stdout, $stderr, $exitCode] = $this->runCli([
            $this->tempDir . '/file.txt',
            $this->tempDir . '/file.txt',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Неподдерживаемый формат файла', $stderr);
    }

    public function testInvalidJson(): void
    {
        file_put_contents($this->tempDir . '/invalid.json', '{invalid json}');
        file_put_contents($this->tempDir . '/valid.json', '{"a": 1}');

        [$stdout, $stderr, $exitCode] = $this->runCli([
            $this->tempDir . '/invalid.json',
            $this->tempDir . '/valid.json',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Ошибка JSON в файле', $stderr);
    }
}