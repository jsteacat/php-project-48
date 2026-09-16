<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;
use Gendiff\Parser;
use Gendiff\Exceptions\UnsupportedFileFormatException;

class ParserTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/gendiff_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tempDir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->tempDir);
    }

    public function testParseJsonFile(): void
    {
        $jsonContent = json_encode(['name' => 'John', 'age' => 30]);
        file_put_contents($this->tempDir . '/test.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/test.json');

        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testParseYamlFile(): void
    {
        $yamlContent = "name: John\nage: 30\n";
        file_put_contents($this->tempDir . '/test.yaml', $yamlContent);

        $result = Parser::parse($this->tempDir . '/test.yaml');

        $this->assertEquals(['name' => 'John', 'age' => 30], $result);
    }

    public function testParseYmlFile(): void
    {
        $yamlContent = "name: Jane\nage: 25\n";
        file_put_contents($this->tempDir . '/test.yml', $yamlContent);

        $result = Parser::parse($this->tempDir . '/test.yml');

        $this->assertEquals(['name' => 'Jane', 'age' => 25], $result);
    }

    public function testParseUnsupportedFileFormat(): void
    {
        file_put_contents($this->tempDir . '/test.txt', 'some content');

        $this->expectException(UnsupportedFileFormatException::class);
        $this->expectExceptionMessageMatches('/Неподдерживаемый формат файла:/');

        Parser::parse($this->tempDir . '/test.txt');
    }

    public function testParseNonExistentFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Файл не найден или недоступен:/');

        Parser::parse($this->tempDir . '/nonexistent.json');
    }

    public function testParseEmptyFile(): void
    {
        file_put_contents($this->tempDir . '/empty.json', '');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Пустой файл:/');

        Parser::parse($this->tempDir . '/empty.json');
    }

    public function testParseInvalidJson(): void
    {
        file_put_contents($this->tempDir . '/invalid.json', '{invalid json}');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ошибка JSON в файле/');

        Parser::parse($this->tempDir . '/invalid.json');
    }

    public function testParseNestedJson(): void
    {
        $jsonContent = json_encode([
            'person' => [
                'name' => 'Alice',
                'address' => [
                    'city' => 'Moscow',
                    'zip' => '123456'
                ]
            ]
        ]);
        file_put_contents($this->tempDir . '/nested.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/nested.json');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('person', $result);
        $this->assertIsArray($result['person']);
    }

    public function testParseJsonWithNullValues(): void
    {
        $jsonContent = json_encode(['key' => null, 'other' => 'value']);
        file_put_contents($this->tempDir . '/null.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/null.json');

        $this->assertEquals(['key' => null, 'other' => 'value'], $result);
    }

    public function testParseJsonWithNumericValues(): void
    {
        $jsonContent = json_encode(['int' => 42, 'float' => 3.14, 'neg' => -10]);
        file_put_contents($this->tempDir . '/numeric.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/numeric.json');

        $this->assertEquals(['int' => 42, 'float' => 3.14, 'neg' => -10], $result);
    }

    public function testParseJsonWithBooleanValues(): void
    {
        $jsonContent = json_encode(['enabled' => true, 'disabled' => false]);
        file_put_contents($this->tempDir . '/bool.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/bool.json');

        $this->assertEquals(['enabled' => true, 'disabled' => false], $result);
    }

    public function testParseYamlWithMultipleDocuments(): void
    {
        $yamlContent = "key1: value1\nkey2: value2\nkey3: value3\n";
        file_put_contents($this->tempDir . '/multi.yaml', $yamlContent);

        $result = Parser::parse($this->tempDir . '/multi.yaml');

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'], $result);
    }

    public function testParseJsonScalarValue(): void
    {
        file_put_contents($this->tempDir . '/scalar.json', '42');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ожидается объект или массив JSON/');

        Parser::parse($this->tempDir . '/scalar.json');
    }

    public function testParseJsonNullValue(): void
    {
        file_put_contents($this->tempDir . '/null.json', 'null');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ожидается объект или массив JSON/');

        Parser::parse($this->tempDir . '/null.json');
    }

    public function testParseJsonString(): void
    {
        file_put_contents($this->tempDir . '/string.json', '"hello"');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ожидается объект или массив JSON/');

        Parser::parse($this->tempDir . '/string.json');
    }

    public function testParseJsonBoolean(): void
    {
        file_put_contents($this->tempDir . '/bool.json', 'true');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ожидается объект или массив JSON/');

        Parser::parse($this->tempDir . '/bool.json');
    }

    public function testParseJsonArray(): void
    {
        $jsonContent = json_encode([1, 2, 3]);
        file_put_contents($this->tempDir . '/array.json', $jsonContent);

        $result = Parser::parse($this->tempDir . '/array.json');

        $this->assertEquals([1, 2, 3], $result);
    }

    public function testParseYamlScalarValue(): void
    {
        file_put_contents($this->tempDir . '/scalar.yaml', 'just a string');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ожидается объект или массив YAML/');

        Parser::parse($this->tempDir . '/scalar.yaml');
    }

    public function testParseInvalidYaml(): void
    {
        file_put_contents($this->tempDir . '/invalid.yaml', "key: [invalid\n  yaml: {structure");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Ошибка YAML в файле/');

        Parser::parse($this->tempDir . '/invalid.yaml');
    }
}
