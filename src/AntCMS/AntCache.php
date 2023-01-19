<?php

namespace AntCMS;

use AntCMS\AntConfig;
use Symfony\Component\Yaml\Exception\ParseException;

class AntCache
{
    /**
     * Caches a value for a given cache key.
     * 
     * @param string $key The cache key to use for the cached value.
     * @param string $content The value to cache.
     * @return bool True if the value was successfully cached, false otherwise.
     * @throws ParseException If there is an error parsing the AntCMS configuration file.
     */
    public function setCache(string $key, string $content)
    {
        $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            try {
                file_put_contents($cachePath, (string)$content);
                return true;
            } catch (\Exception) {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Retrieves the cached value for a given cache key.
     * 
     * @param string $key The cache key used to retrieve the cached value.
     * @return string|false The cached value, or false if there was an error loading it or if caching is disabled.
     * @throws ParseException If there is an error parsing the AntCMS configuration file.
     */
    public function getCache(string $key)
    {
        $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            try {
                return file_get_contents($cachePath);
            } catch (\Exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Determines if a cache key has a corresponding cached value.
     * 
     * @param string $key The cache key to check.
     * @return bool True if the cache key has a corresponding cached value, false otherwise. Will also return false if caching is disabled.
     * @throws ParseException If there is an error parsing the AntCMS configuration file.
     */
    public function isCached(string $key)
    {
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
            return file_exists($cachePath);
        } else {
            return false;
        }
    }

    /**
     * Generates a unique cache key for the associated content and a salt value.
     * The salt is used to ensure that each cache key is unique to each component, even if multiple components are using the same source content but caching different results.
     * 
     * @param string $content The content to generate a cache key for.
     * @param string $salt An optional salt value to use in the cache key generation. Default is 'cache'.
     * @return string The generated cache key.
     */
    public function createCacheKey(string $content, string $salt = 'cache')
    {
        /**
         * If the server is modern enough to have xxh128, use that. It is really fast and still produces long hashes
         * If not, use MD4 since it's still quite fast.
         * Source: https://php.watch/articles/php-hash-benchmark
         */
        if (in_array('xxh128', hash_algos())) {
            return hash('xxh128', $content . $salt);
        } else {
            return hash('md4', $content . $salt);
        }
    }
}
