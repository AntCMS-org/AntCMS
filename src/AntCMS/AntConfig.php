<?php

namespace AntCMS;

use AntCMS\AntYaml;
use Exception;

class AntConfig
{
    private static array $ConfigKeys = [
        'siteInfo',
        'forceHTTPS',
        'activeTheme',
        'cacheMode',
        'debug',
        'baseURL',
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
            'forceHTTPS' => true,
            'activeTheme' => 'Default',
            'cacheMode' => 'auto',
            'debug' => true,
            'baseURL' => $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']),
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
    public static function currentConfig(?string $key = null)
    {
        // FS cache enabled to save ~10% of the time to deliver the file page.
        $config = AntYaml::parseFile(antConfigFile, true);
        if (is_null($key)) {
            return $config;
        } else {
            $keys = explode('.', $key);
            return self::getArrayValue($config, $keys);
        }
    }

    /**
     * @param array<mixed> $array
     * @param array<mixed> $keys
     * @return mixed
     */
    private static function getArrayValue(array $array, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                return $array[$key];
            } else {
                return null;
            }
        }
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
                throw new Exception("New config is missing the required {$ConfigKey} key from it's array!");
            }
        }

        return AntYaml::saveFile(antConfigFile, $config);
    }
}
