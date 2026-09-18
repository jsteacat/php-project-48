<?php

namespace Gendiff\Formatters;

use Gendiff\NodeType;

interface Formatter
{
    /**
     * Собирает diff в текстовое представление конкретного формата.
     *
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed}> $diff
     *        узел: тип узла + value | oldValue/newValue
     */
    public function format(array $diff): string;
}
