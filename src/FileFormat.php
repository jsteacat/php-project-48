<?php

declare(strict_types=1);

namespace Gendiff;

/**
 * Форматы файлов, содержимое которых умеет разбирать парсер.
 *
 * Значение case совпадает с расширением файла.
 */
enum FileFormat: string
{
    case Json = 'json';
    case Yaml = 'yaml';
    case Yml = 'yml';

    /**
     * Список поддерживаемых расширений — для сообщений об ошибке и документации.
     *
     * @return string[]
     */
    public static function extensions(): array
    {
        return array_map(static fn (self $format): string => $format->value, self::cases());
    }
}
