<?php

declare(strict_types=1);

namespace Gendiff\Exceptions;

use RuntimeException;

/**
 * Формат вывода, которого нет среди OutputFormat.
 */
class UnsupportedFormatException extends RuntimeException
{
    public static function forFormat(string $format): self
    {
        return new self("Неподдерживаемый формат вывода: $format");
    }
}
