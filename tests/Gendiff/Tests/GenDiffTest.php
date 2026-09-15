<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\genDiff;

class GenDiffTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/fixtures';
        if (!is_dir($this->fixturesDir)) {
            mkdir($this->fixturesDir, 0777, true);
        }
    }

    public function testIdenticalFiles(): void
    {
        file_put_contents($this->fixturesDir . '/identical1.json', '{"a": 1, "b": 2}');
        file_put_contents($this->fixturesDir . '/identical2.json', '{"a": 1, "b": 2}');

        $result = genDiff($this->fixturesDir . '/identical1.json', $this->fixturesDir . '/identical2.json');

        $expected = <<<OUTPUT
{
  a: 1
  b: 2
}
OUTPUT;

        $this->assertSame($expected, $result);
    }

    public function testKeysOnlyInFirstFile(): void
    {
        file_put_contents($this->fixturesDir . '/first_only1.json', '{"a": 1, "b": 2}');
        file_put_contents($this->fixturesDir . '/first_only2.json', '{"a": 1}');

        $result = genDiff($this->fixturesDir . '/first_only1.json', $this->fixturesDir . '/first_only2.json');

        $expected = <<<OUTPUT
{
  a: 1
  - b: 2
}
OUTPUT;

        $this->assertSame($expected, $result);
    }

    public function testKeysOnlyInSecondFile(): void
    {
        file_put_contents($this->fixturesDir . '/second_only1.json', '{"a": 1}');
        file_put_contents($this->fixturesDir . '/second_only2.json', '{"a": 1, "b": 2}');

        $result = genDiff($this->fixturesDir . '/second_only1.json', $this->fixturesDir . '/second_only2.json');

        $expected = <<<OUTPUT
{
  a: 1
  + b: 2
}
OUTPUT;

        $this->assertSame($expected, $result);
    }

    public function testDifferentValues(): void
    {
        file_put_contents($this->fixturesDir . '/diff_values1.json', '{"a": 1, "b": 2}');
        file_put_contents($this->fixturesDir . '/diff_values2.json', '{"a": 1, "b": 3}');

        $result = genDiff($this->fixturesDir . '/diff_values1.json', $this->fixturesDir . '/diff_values2.json');

        $expected = <<<OUTPUT
{
  a: 1
  - b: 2
  + b: 3
}
OUTPUT;

        $this->assertSame($expected, $result);
    }

    public function testMixedChanges(): void
    {
        file_put_contents($this->fixturesDir . '/mixed1.json', '{"a": 1, "b": 2, "c": 3}');
        file_put_contents($this->fixturesDir . '/mixed2.json', '{"a": 1, "b": 5, "d": 4}');

        $result = genDiff($this->fixturesDir . '/mixed1.json', $this->fixturesDir . '/mixed2.json');

        $expected = <<<OUTPUT
{
  a: 1
  - b: 2
  + b: 5
  - c: 3
  + d: 4
}
OUTPUT;

        $this->assertSame($expected, $result);
    }

    public function testKeysAreSorted(): void
    {
        file_put_contents($this->fixturesDir . '/sorted1.json', '{"z": 1, "a": 2, "m": 3}');
        file_put_contents($this->fixturesDir . '/sorted2.json', '{"z": 1, "a": 2, "m": 4}');

        $result = genDiff($this->fixturesDir . '/sorted1.json', $this->fixturesDir . '/sorted2.json');

        // Keys should be sorted: a, m, z
        $this->assertMatchesRegularExpression('/a: 2\s+- m: 3\s+\+ m: 4\s+z: 1/', $result);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->fixturesDir . '/*.json') as $file) {
            unlink($file);
        }
    }
}
