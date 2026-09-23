<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use InvalidArgumentException;

/**
 * Файл существует, но пуст: разбирать нечего.
 */
class EmptyFileException extends InvalidArgumentException
{
    public static function forPath(string $filePath): self
    {
        return new self("Пустой файл: $filePath");
    }
}
