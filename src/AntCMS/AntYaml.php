<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * This class acts as a fairly simple wrapper for the Symfony YAML component.
 * An in-memory cache is utilized to prevent multiple parsings for the same file in a single request.
 */
class AntYaml
{
    /** @var array<string, mixed> $yamlCache An in-memory cache for parsed YAML files to prevent repeat hits from slowing down AntCMS. */
    private static array $yamlCache = [];

    /**
     * Parses a YAML file and returns the content as an array.
     * @throws ParseException
     * @return mixed[]
     */
    public static function parseFile(string $path): array
    {
        $cacheKey = hash('xxh3', $path);
        self::$yamlCache[$cacheKey] ??= Yaml::parseFile($path);
        return self::$yamlCache[$cacheKey];
    }

    /**
     * Takes an array and dumps it as a YAML file.
     * When files are dumped, the data will automatically be loaded into the in-memory cache.
     *
     * @param string $path The file path to save
     * @param array<mixed> $data The array of data to be converted to YAML and then dumped
     */
    public static function saveFile(string $path, array $data): bool
    {
        // First update / set the cached data for this file
        $cacheKey = hash('xxh3', $path);
        self::$yamlCache[$cacheKey] = $data;

        // Then we can actually convert it to YAML and dump it
        $yaml = Yaml::dump($data);
        return (bool) file_put_contents($path, $yaml);
    }

    /**
     * Parses a string containing YAML data and returns the content as an array.
     * @return mixed[]
     */
    public static function parseYaml(string $yaml): array
    {
        return Yaml::parse($yaml);
    }
}
