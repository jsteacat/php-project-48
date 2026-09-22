<?php

declare(strict_types=1);

namespace Gendiff;

use Gendiff\Exceptions\UnsupportedFormatException;

/**
 * Формат вывода diff.
 *
 * Значение case совпадает с тем, что пользователь передаёт в опцию --format
 * и третьим аргументом в genDiff().
 */
enum OutputFormat: string
{
    case Stylish = 'stylish';
    case Plain = 'plain';

    /**
     * Приводит строку из CLI или публичного API к формату вывода.
     *
     * @throws UnsupportedFormatException если формат не поддерживается
     */
    public static function fromString(string $format): self
    {
        return self::tryFrom($format)
            ?? throw new UnsupportedFormatException("Неподдерживаемый формат вывода: $format");
    }
}
