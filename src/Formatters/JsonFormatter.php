<?php

declare(strict_types=1);

namespace Gendiff\Formatters;

use Gendiff\NodeType;

/**
 * Рисует diff как JSON промежуточного представления.
 *
 * Пустой diff — это пустой список `[]`, иначе объект со всеми узлами:
 * json_encode сериализует backed enum NodeType его значением.
 */
class JsonFormatter implements Formatter
{
    /**
     * @param array<int|string, array{type: NodeType, value?: mixed, oldValue?: mixed, newValue?: mixed,
     *     children?: array<int|string, mixed>}> $diff
     */
    public function format(array $diff): string
    {
        $json = json_encode(
            $diff,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return $json === false ? '[]' : $json;
    }
}
