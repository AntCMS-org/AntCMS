<?php

namespace AntCMS;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class Cache
{
    private ?object $CacheInterface = null;
    public static int $defaultLifespan = 604_800; // 1 week

    /**
     * Creates a new cache object, sets the correct caching type. ('auto', 'filesystem', 'apcu', or 'none')
     */
    public function __construct(null|string $mode = null)
    {
        $mode ??= Config::get('cacheMode') ?? 'auto';
        if ($mode == 'auto') {
            if (function_exists('apcu_enabled') && apcu_enabled()) {
                $mode = 'apcu';
            } else {
                $mode = 'filesystem';
            }
        }

        $this->CacheInterface = match ($mode) {
            'none' => new ArrayAdapter(0, true, 0, 150),
            'filesystem' => new FilesystemAdapter('', self::$defaultLifespan, AntCachePath),
            'apcu' => new ApcuAdapter('AntCMS_' . hash('md5', __DIR__), self::$defaultLifespan),
            default => throw new \Exception("Invalid cache type. Must be 'auto', 'filesystem', 'apcu', or 'none'."),
        };
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->CacheInterface, $name)) {
            return call_user_func_array([$this->CacheInterface, $name], $arguments);
        }
        return false;
    }

    /**
     * Generates a unique cache key for the associated content and a salt value.
     * The salt is used to ensure that each cache key is unique to each component, even if multiple components are using the same source content but caching different results.
     *
     * @param string $content The content to generate a cache key for.
     * @param string $salt An optional salt value to use in the cache key generation. Default is 'cache'.
     * @return string The generated cache key.
     */
    public function createCacheKey(string $content, string $salt = 'cache'): string
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
    public function createCacheKeyFile(string $filePath, string $salt = 'cache'): string
    {
        $differentiator = filemtime($filePath) ?: hash_file(HASH_ALGO, $filePath);
        return hash(HASH_ALGO, $filePath) . ".$differentiator.$salt";
    }
}
