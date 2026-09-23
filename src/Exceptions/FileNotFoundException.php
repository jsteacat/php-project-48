<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use InvalidArgumentException;

/**
 * Файла нет или он недоступен для чтения.
 */
class FileNotFoundException extends InvalidArgumentException
{
    public static function forPath(string $filePath): self
    {
        return new self("Файл не найден или недоступен: $filePath");
    }
}
