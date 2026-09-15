<?php

namespace Gendiff;

use Symfony\Component\Yaml\Yaml;

class Parser
{
    public static function parse(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return match ($extension) {
            'json' => json_decode($content, true),
            'yaml', 'yml' => Yaml::parse($content),
            default => throw new \Gendiff\Exceptions\UnsupportedFileFormatException(
                "Неподдерживаемый формат: {$extension}"
            ),
        };
    }
}
