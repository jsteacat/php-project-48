<?php

namespace Gendiff;

/**
 * Тип узла diff — что произошло со значением ключа при сравнении файлов.
 *
 * Enum вместо строк: набор закрыт, а match по нему проверяется статически.
 */
enum NodeType: string
{
    case Unchanged = 'unchanged';
    case Removed = 'removed';
    case Added = 'added';
    case Changed = 'changed';
}
