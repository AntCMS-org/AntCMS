<?php

namespace AntCMS;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\ItemInterface;

class AntYaml
{
    public static function parseFile(string $file, bool $fileCache = false): array
    {
        if ($fileCache) {
            $antCache = new Cache('filesystem');
        } else {
            $antCache = new Cache();
        }

        $cacheKey = $antCache->createCacheKeyFile($file);
        return $antCache->get($cacheKey, function (ItemInterface $item) use ($file): array {
            $item->expiresAfter(Cache::$defaultLifespan / 7);
            return Yaml::parseFile($file);
        });
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
