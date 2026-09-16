<?php

namespace Gendiff;

/**
 * Константы проекта: форматы вывода, поддерживаемые расширения и типы узлов diff.
 *
 * Класс, а не набор const в файле: файл из autoload.files загружается
 * до старта сбора покрытия, и такие строки навсегда остаются непокрытыми.
 */
final class Constants
{
    public const FORMAT_STYLISH = 'stylish';
    public const DEFAULT_FORMAT = self::FORMAT_STYLISH;

    public const SUPPORTED_EXTENSIONS = ['json', 'yaml', 'yml'];

    public const NODE_UNCHANGED = 'unchanged';
    public const NODE_REMOVED = 'removed';
    public const NODE_ADDED = 'added';
    public const NODE_CHANGED = 'changed';
}
