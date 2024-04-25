<?php

namespace AntCMS;

use Exception;

class Config
{
    private static array $ConfigKeys = [
        'siteInfo',
        'performance',
        'forceHttps',
        'activeTheme',
        'cacheMode',
        'debug',
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
                'siteTitle' => 'AntCMS',
            ],
            'performance' => [
                'doOutputCompression' => true,
                'compressTextAssets' => true,
                'compressImageAssets' => true,
            ],
            'forceHttps' => !Enviroment::isPHPDevServer(),
            'activeTheme' => 'Default',
            'cacheMode' => 'auto',
            'debug' => true,
            'baseUrl' => $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']),
            'embed' => [
                'allowed_domains' => ['youtube.com', 'twitter.com', 'github.com', 'vimeo.com', 'flickr.com', 'instagram.com', 'facebook.com'],
            ]
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
