<?php

namespace Gendiff\Formatters;

interface Formatter
{
    /**
     * Собирает diff в текстовое представление конкретного формата.
     *
     * @param array<int|string, array<string, mixed>> $diff узел: type + value | oldValue/newValue
     */
    public function format(array $diff): string;
}
