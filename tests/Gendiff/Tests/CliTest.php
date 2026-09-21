<?php

declare(strict_types=1);

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Смоук-тесты настоящего процесса bin/gendiff: он должен запускаться
 * и корректно возвращать код выхода.
 */
class CliTest extends TestCase
{
    private const BIN_PATH = __DIR__ . '/../../../bin/gendiff';
    private const FIXTURES_DIR = __DIR__ . '/../../fixtures';

    /**
     * @param string[] $args
     *
     * @return array{0: string, 1: string, 2: int} stdout, stderr, код возврата
     */
    private function runCli(array $args): array
    {
        $process = proc_open(
            array_merge([PHP_BINARY, self::BIN_PATH], $args),
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

    public function testDiffOfFlatJsonFiles(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            self::FIXTURES_DIR . '/file1.json',
            self::FIXTURES_DIR . '/file2.json',
        ]);

        $expected = (string) file_get_contents(self::FIXTURES_DIR . '/expected/stylish.txt');

        $this->assertSame(0, $exitCode);
        $this->assertSame($expected, $stdout);
        $this->assertSame('', $stderr);
    }

    public function testErrorIsWrittenToStderr(): void
    {
        [$stdout, $stderr, $exitCode] = $this->runCli([
            self::FIXTURES_DIR . '/missing.json',
            self::FIXTURES_DIR . '/file1.json',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertSame('', $stdout);
        $this->assertStringContainsString('Файл не найден или недоступен', $stderr);
    }
}
