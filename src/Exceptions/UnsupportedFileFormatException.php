<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use Gendiff\FileFormat;
use RuntimeException;

/**
 * Расширение файла не поддержано парсером.
 */
class UnsupportedFileFormatException extends RuntimeException
{
    public static function forPath(string $filePath): self
    {
        return new self(
            "Неподдерживаемый формат файла: $filePath. Поддерживаются: "
                . implode(', ', FileFormat::extensions())
        );
    }
}
