<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use InvalidArgumentException;

/**
 * Содержимое файла не разбирается: синтаксическая ошибка JSON или YAML.
 */
class MalformedFileException extends InvalidArgumentException
{
    public static function json(string $filePath, string $reason): self
    {
        return new self("Ошибка JSON в файле $filePath: $reason");
    }

    public static function yaml(string $filePath, string $reason): self
    {
        return new self("Ошибка YAML в файле $filePath: $reason");
    }
}
