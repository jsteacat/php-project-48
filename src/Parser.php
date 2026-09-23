<?php

declare(strict_types=1);

namespace Gendiff;

use Gendiff\Exceptions\EmptyFileException;
use Gendiff\Exceptions\FileNotFoundException;
use Gendiff\Exceptions\MalformedFileException;
use Gendiff\Exceptions\UnexpectedStructureException;
use Gendiff\Exceptions\UnsupportedFileFormatException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Parser
{
    public static function parse(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw FileNotFoundException::forPath($filePath);
        }

        $format = FileFormat::tryFrom(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($format === null) {
            throw UnsupportedFileFormatException::forPath($filePath);
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
            throw EmptyFileException::forPath($filePath);
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw MalformedFileException::json($filePath, json_last_error_msg());
        }

        if (!is_array($data)) {
            throw UnexpectedStructureException::forJson($filePath, get_debug_type($data));
        }

        return $data;
    }

    private static function parseYaml(string $content, string $filePath): array
    {
        try {
            $data = Yaml::parse($content);
        } catch (ParseException $e) {
            throw MalformedFileException::yaml($filePath, $e->getMessage());
        }

        if (!is_array($data)) {
            throw UnexpectedStructureException::forYaml($filePath, get_debug_type($data));
        }

        return $data;
    }
}
