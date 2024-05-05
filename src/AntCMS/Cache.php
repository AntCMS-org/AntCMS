<?php

namespace AntCMS;

use Symfony\Component\Cache\Adapter\{ApcuAdapter, ArrayAdapter, ChainAdapter, FilesystemAdapter, PhpFilesAdapter};
use Symfony\Contracts\Cache\CallbackInterface;

class Cache
{
    private static ArrayAdapter|ChainAdapter $adapter;
    public static int $longLifespan = 86400 * 30; // 1 month (essentially cold-storage items)
    public static int $mediumLifespan = 86400 * 7; // 1 week
    public static int $shortLifespan = 900; // 15 min (in-memory)

    public static function setup(array $allowed = []): void
    {
        $adapters = [];

        // Register cache adapters in order of fastest to slowest
        if (in_array('apcu', $allowed) && ApcuAdapter::isSupported()) {
            $adapters[] = new ApcuAdapter('AntCMS_' . hash(HASH_ALGO, PATH_ROOT), self::$shortLifespan);
        }

        if (in_array('php_file', $allowed) && PhpFilesAdapter::isSupported()) {
            $adapters[] = new PhpFilesAdapter('php_files', self::$mediumLifespan, PATH_CACHE);
        }

        if (in_array('filesystem', $allowed)) {
            $adapters[] = new FilesystemAdapter('filesystem', self::$longLifespan, PATH_CACHE);
        }

        if ($adapters === []) {
            self::$adapter = new ArrayAdapter();
        } else {
            self::$adapter = new ChainAdapter($adapters);
        }
    }

    public static function get(string $key, callable|CallbackInterface $callable, ?float $beta = null, ?array &$metadata = []): mixed
    {
        return self::$adapter->get($key, $callable, $beta, $metadata);
    }

    public static function prune(): bool
    {
        return self::$adapter->prune();
    }

    /**
     * Generates a unique cache key for the associated content and a salt value.
     * The salt is used to ensure that each cache key is unique to each component, even if multiple components are using the same source content but caching different results.
     *
     * @param string $content The content to generate a cache key for.
     * @param string $salt An optional salt value to use in the cache key generation. Default is 'cache'.
     * @return string The generated cache key.
     */
    public static function createCacheKey(string $content, string $salt = 'cache'): string
    {
        return hash(HASH_ALGO, $content . $salt);
    }

    /**
     * Generates a unique cache key for a file and a salt value.
     * The salt is used to ensure that each cache key is unique to each component, even if multiple components are using the same source content but caching different results.
     *
     * @param string $filePath The file path to create a cache key for.
     * @param string $salt An optional salt value to use in the cache key generation. Default is 'cache'.
     * @return string The generated cache key.
     */
    public static function createCacheKeyFile(string $filePath, string $salt = 'cache'): string
    {
        $differentiator = filemtime($filePath) ?: hash_file(HASH_ALGO, $filePath);
        return hash(HASH_ALGO, $filePath . ".$differentiator.$salt");
    }
}
