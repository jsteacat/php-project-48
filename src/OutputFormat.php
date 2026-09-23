<?php

declare(strict_types=1);

namespace Gendiff;

/**
 * Формат вывода diff.
 *
 * Enum намеренно ничего не знает про валидацию: он только сопоставляет строку
 * с case'ом, а неизвестный формат обрабатывает вызывающий код — genDiff().
 *
 * Значение case совпадает с тем, что пользователь передаёт в опцию --format
 * и третьим аргументом в genDiff().
 */
enum OutputFormat: string
{
    case Stylish = 'stylish';
    case Plain = 'plain';
    case Json = 'json';
}
