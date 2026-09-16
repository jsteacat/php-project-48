<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;

use function Gendiff\genDiff;

class GenDiffTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/../../fixtures';
    }

    public function testJsonFiles(): void
    {
        $result = genDiff($this->fixturesDir . '/file1.json', $this->fixturesDir . '/file2.json');

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

        $this->assertSame($expected, $result);
    }

    public function testYamlFiles(): void
    {
        $result = genDiff($this->fixturesDir . '/file1.yaml', $this->fixturesDir . '/file2.yml');

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

        $this->assertSame($expected, $result);
    }
}
