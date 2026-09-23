<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use InvalidArgumentException;

/**
 * Файл разобран, но на верхнем уровне не объект и не массив.
 */
class UnexpectedStructureException extends InvalidArgumentException
{
    public static function forJson(string $filePath, string $actualType): self
    {
        return new self("Ожидается объект или массив JSON в файле $filePath, получен: $actualType");
    }

    public static function forYaml(string $filePath, string $actualType): self
    {
        return new self("Ожидается объект или массив YAML в файле $filePath, получен: $actualType");
    }
}
