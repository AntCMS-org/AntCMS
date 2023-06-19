<?php

namespace AntCMS;

use AntCMS\AntConfig;
use Symfony\Component\Yaml\Exception\ParseException;

class AntCache
{
    private int $cacheType = 0;
    private string $cacheKeyApcu = '';

    const noCache   = 0;
    const fileCache = 1;
    const apcuCache = 2;

    /**
     * Creates a new cache object, sets the correct caching type. ('auto', 'filesystem', 'apcu', or 'none')
     */
    public function __construct(null|string $mode = null)
    {
        $mode = $mode ?? AntConfig::currentConfig('cacheMode') ?? 'auto';

        switch ($mode) {
            case 'none':
                $this->cacheType = self::noCache;
                break;
            case 'auto':
                if (extension_loaded('apcu') && apcu_enabled()) {
                    $this->cacheType = self::apcuCache;
                    $this->cacheKeyApcu = 'AntCMS_' . hash('md5', __DIR__) . '_';
                } else {
                    $this->cacheType = self::fileCache;
                }
                break;
            case 'filesystem':
                $this->cacheType = self::fileCache;
                break;
            case 'apcu':
                $this->cacheType = self::apcuCache;
                $this->cacheKeyApcu = 'AntCMS_' . hash('md5', __DIR__) . '_';
                break;
            default:
                throw new \Exception("Invalid cache type. Must be 'auto', 'filesystem', 'apcu', or 'none'.");
        }
    }

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
        switch ($this->cacheType) {
            case self::noCache:
                return false;
            case self::fileCache:
                $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
                return file_put_contents($cachePath, $content);
            case self::apcuCache:
                $apcuKey = $this->cacheKeyApcu . $key;
                return apcu_store($apcuKey, $content, 7 * 24 * 60 * 60); // Save it for one week.
            default:
                return false;
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
        switch ($this->cacheType) {
            case self::noCache:
                return false;
            case self::fileCache:
                $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
                return file_get_contents($cachePath);
            case self::apcuCache:
                $apcuKey = $this->cacheKeyApcu . $key;
                $success = false;
                $result = apcu_fetch($apcuKey, $success);
                return $success ? $result : false;
            default:
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
        switch ($this->cacheType) {
            case self::noCache:
                return false;
            case self::fileCache:
                $cachePath = AntCachePath . DIRECTORY_SEPARATOR . "{$key}.cache";
                return file_exists($cachePath);
            case self::apcuCache:
                $apcuKey = $this->cacheKeyApcu . $key;
                return apcu_exists($apcuKey);
            default:
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
        return hash(self::getHashAlgo(), $content . $salt);
    }

    /**
     * Generates a unique cache key for a file and a salt value.
     * The salt is used to ensure that each cache key is unique to each component, even if multiple components are using the same source content but caching different results.
     * 
     * @param string $filePath The file path to create a cache key for.
     * @param string $salt An optional salt value to use in the cache key generation. Default is 'cache'.
     * @return string The generated cache key.
     */
    public function createCacheKeyFile(string $filePath, string $salt = 'cache')
    {
        return hash_file(self::getHashAlgo(), $filePath) . $salt;
    }

    public static function clearCache(): void
    {
        $di = new \RecursiveDirectoryIterator(AntCachePath, \FilesystemIterator::SKIP_DOTS);
        $ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ?  rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        if (extension_loaded('apcu') && apcu_enabled()) {
            $prefix = 'AntCMS_' . hash('md5', __DIR__) . '_';
            $cacheInfo = apcu_cache_info();
            $keys = $cacheInfo['cache_list'];

            foreach ($keys as $keyInfo) {
                $key = $keyInfo['info'];
                if (str_starts_with($key, $prefix)) {
                    apcu_delete($key);
                }
            }
        }
    }

    public static function getHashAlgo(): string
    {
        /**
         * If the server is modern enough to have xxh128, use that. It is really fast and still produces long hashes
         * If not, use MD4 since it's still quite fast.
         * Source: https://php.watch/articles/php-hash-benchmark
         */
        return defined('HAS_XXH128') ? 'xxh128' : 'md4';
    }
}
