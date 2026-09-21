<?php

declare(strict_types=1);

namespace Gendiff\Formatters;

use Gendiff\NodeType;

interface Formatter
{
    /**
     * Собирает diff в текстовое представление конкретного формата.
     *
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
     *     children?: array<int|string, mixed>}> $diff
     *        узел: тип узла + value | oldValue/newValue | children у вложенного узла
     */
    public function format(array $diff): string;
}
