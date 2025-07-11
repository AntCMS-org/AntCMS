<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use Exception;

class Config
{
    /** @var string[] */
    private static array $ConfigKeys = [
        'siteInfo',
        'performance',
        'forceHttps',
        'activeTheme',
        'debugLevel',
        'baseUrl',
        'embed',
    ];

    /**
     * Generates the default config file and saves it.
     */
    public static function generateConfig(): void
    {
        $defaultOptions = [
            'siteInfo' => [
                'title' => 'AntCMS',
            ],
            'performance' => [
                'doOutputCompression' => true,
                'compressTextAssets' => true,
                'compressImageAssets' => true,
                'imageQuality' => 85,
                'allowedCacheMethods' => ['acpu', 'php_files', 'filesystem'],
            ],
            'forceHttps' => !Enviroment::isPHPDevServer(),
            'activeTheme' => 'Default',
            'debugLevel' => 1, // 0-2 at the moment
            'baseUrl' => $_SERVER['HTTP_HOST'] . dirname((string) $_SERVER['PHP_SELF']),
            'embed' => [
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com', 'vimeo.com', 'flickr.com', 'instagram.com', 'facebook.com'],
            ],
        ];

        self::saveConfig($defaultOptions);
    }

    /**
     * Retrieves the current configuration from the AntCMS config file.
     *
     * @param string|null $key The key of the configuration item to retrieve. Use dot notation to specify nested keys.
     * @return mixed The configuration array or a specific value if the key is specified.
     */
    public static function get(?string $key = null): mixed
    {
        $config = AntYaml::parseFile(PATH_CONFIG);
        if (is_null($key)) {
            return $config;
        }
        foreach (explode('.', $key) as $segment) {
            if (array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return null;
            }
        }
        return $config;
    }

    /**
     * Saves the AntCMS configuration
     *
     * @param array<mixed> $config The config data to be saved.
     * @throws exception
     */
    public static function saveConfig(array $config): bool
    {
        foreach (self::$ConfigKeys as $ConfigKey) {
            if (!array_key_exists($ConfigKey, $config)) {
                throw new Exception("New config is missing the required {$ConfigKey} key from its array!");
            }
        }

        return AntYaml::saveFile(PATH_CONFIG, $config);
    }
}
