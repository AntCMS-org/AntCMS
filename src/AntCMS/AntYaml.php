<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AntYaml
{
    private static array $yamlCache = [];

    public static function parseFile(string $path): array
    {
        $cacheKey = hash(HASH_ALGO, $path);
        self::$yamlCache[$cacheKey] ??= Yaml::parseFile($path);
        return self::$yamlCache[$cacheKey];
    }

    /**
     * @param array<mixed> $data
     */
    public static function saveFile(string $file, array $data): bool
    {
        $yaml = Yaml::dump($data);
        return (bool) file_put_contents($file, $yaml);
    }

    /**
     * @return array<mixed>|null
     */
    public static function parseYaml(string $yaml): ?array
    {
        try {
            return Yaml::parse($yaml);
        } catch (ParseException) {
            return null;
        }
    }
}
