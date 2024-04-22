<?php

namespace AntCMS;

use AntCMS\AntYaml;
use AntCMS\Enviroment;
use Exception;

class Config
{
    private static array $ConfigKeys = [
        'siteInfo',
        'performance',
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
            'performance' => [
                'doOutputCompression' => true,
                'compressTextAssets' => true,
            ],
            'forceHTTPS' => !Enviroment::isPHPDevServer(),
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
    public static function get(?string $key = null)
    {
        $config = AntYaml::parseFile(antConfigFile);
        if (is_null($key)) {
            return $config;
        } else {
            foreach (explode('.', $key) as $segment) {
                if (array_key_exists($segment, $config)) {
                    $config = $config[$segment];
                } else {
                    return null;
                }
            }

            return $config;
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
                throw new Exception("New config is missing the required {$ConfigKey} key from its array!");
            }
        }

        return AntYaml::saveFile(antConfigFile, $config);
    }
}
