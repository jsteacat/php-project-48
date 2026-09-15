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
            'json' => self::parseJson($content),
            'yaml', 'yml' => self::parseYaml($content),
            default => throw new \Gendiff\Exceptions\UnsupportedFileFormatException(
                "Неподдерживаемый формат: {$extension}"
            ),
        };
    }

    private static function parseJson(string $content): array
    {
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                "Ошибка JSON: " . json_last_error_msg()
            );
        }

        return $data;
    }

    private static function parseYaml(string $content): array
    {
        $data = Yaml::parse($content);

        return $data ?? [];
    }
}
