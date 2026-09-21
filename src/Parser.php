<?php

declare(strict_types=1);

namespace Gendiff;

use Gendiff\Exceptions\UnsupportedFileFormatException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Parser
{
    public static function parse(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException(
                "Файл не найден или недоступен: {$filePath}"
            );
        }

        $format = FileFormat::tryFrom(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($format === null) {
            throw new UnsupportedFileFormatException(
                "Неподдерживаемый формат файла: {$filePath}. Поддерживаются: "
                    . implode(', ', FileFormat::extensions())
            );
        }

        $content = file_get_contents($filePath);

        return match ($format) {
            FileFormat::Json => self::parseJson($content, $filePath),
            FileFormat::Yaml, FileFormat::Yml => self::parseYaml($content, $filePath),
        };
    }

    private static function parseJson(string $content, string $filePath): array
    {
        if ($content === '') {
            throw new \InvalidArgumentException("Пустой файл: {$filePath}");
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException(
                "Ошибка JSON в файле {$filePath}: " . json_last_error_msg()
            );
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                "Ожидается объект или массив JSON в файле {$filePath}, получен: " . get_debug_type($data)
            );
        }

        return $data;
    }

    private static function parseYaml(string $content, string $filePath): array
    {
        try {
            $data = Yaml::parse($content);
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(
                "Ошибка YAML в файле {$filePath}: " . $e->getMessage()
            );
        }

        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                "Ожидается объект или массив YAML в файле {$filePath}, получен: " . get_debug_type($data)
            );
        }

        return $data;
    }
}
