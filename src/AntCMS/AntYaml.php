<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use Symfony\Component\Filesystem\Filesystem;
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
     *
     * @param string $path A path to the yaml file to parse
     * @param bool $fresh set to true to refresh the YAML cash for the file, ensuring fresh data
     * @throws ParseException
     * @return mixed[]
     */
    public static function parseFile(string $path, bool $fresh = false): array
    {
        $cacheKey = hash('crc32', $path);

        if ($fresh) {
            self::$yamlCache[$cacheKey] = Yaml::parseFile($path);
        }

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
        $cacheKey = hash('crc32', $path);
        self::$yamlCache[$cacheKey] = $data;

        // Then we can actually convert it to YAML and dump it
        $yaml = Yaml::dump($data);

        $filesystem = new Filesystem();
        $filesystem->dumpFile($path, $yaml);
        return true;
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
