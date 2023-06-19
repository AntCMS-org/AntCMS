<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class AntYaml
{
    public static function parseFile(string $file, bool $fileCache = false): array
    {
        if ($fileCache) {
            $antCache = new AntCache('filesystem');
        } else {
            $antCache = new AntCache();
        }

        $cacheKey = $antCache->createCacheKeyFile($file);
        if ($antCache->isCached($cacheKey)) {
            $parsed = json_decode($antCache->getCache($cacheKey), true);
        }

        if (empty($parsed)) {
            $parsed = Yaml::parseFile($file);
            $antCache->setCache($cacheKey, json_encode($parsed));
        }

        return $parsed;
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
