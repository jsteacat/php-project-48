<?php

namespace Gendiff\Tests;

use PHPUnit\Framework\TestCase;
use function Gendiff\stringify;

class StringifyTest extends TestCase
{
    public function testStringifyBooleanTrue(): void
    {
        $this->assertSame('true', stringify(true));
    }

    public function testStringifyBooleanFalse(): void
    {
        $this->assertSame('false', stringify(false));
    }

    public function testStringifyNull(): void
    {
        $this->assertSame('null', stringify(null));
    }

    public function testStringifyInteger(): void
    {
        $this->assertSame('42', stringify(42));
    }

    public function testStringifyFloat(): void
    {
        $this->assertSame('3.14', stringify(3.14));
    }

    public function testStringifyString(): void
    {
        $this->assertSame('hello', stringify('hello'));
    }

    public function testStringifyArrayThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Массивы не поддерживаются');

        stringify(['key' => 'value']);
    }
}
