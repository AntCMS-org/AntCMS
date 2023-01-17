<?php

namespace AntCMS;

use AntCMS\AntYaml;
use Exception;

class AntConfig
{
    private static $ConfigKeys = [
        'siteInfo',
        'forceHTTPS',
        'activeTheme',
        'generateKeywords',
        'enableCache',
        'admin',
        'debug',
        'baseURL',
    ];

    /**
     * Generates the default config file and saves it.
     * @return void
     */
    public static function generateConfig()
    {
        $defaultOptions = [
            'siteInfo' => [
                'siteTitle' => 'AntCMS',
            ],
            'forceHTTPS' => true,
            'activeTheme' => 'Default',
            'generateKeywords' => false,
            'enableCache' => true,
            'admin' =>  array(
                'username' => 'Admin',
                'password' => '',
            ),
            'debug' => true,
            'baseURL' => $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']),
        ];

        Self::saveConfig($defaultOptions);
    }

    /**
     * Retrieves the current configuration from the AntCMS config file.
     * 
     * @param string|null $key The key of the configuration item to retrieve. Use dot notation to specify nested keys.
     * @return mixed The configuration array or a specific value if the key is specified.
     */
    public static function currentConfig(?string $key = null)
    {
        $config = AntYaml::parseFile(antConfigFile);
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
                $array = $array[$key];
            } else {
                return null;
            }
        }

        return $array;
    }

    /**
     * Saves the AntCMS configuration
     * 
     * @param array<mixed> $config The config data to be saved.
     * @return bool
     * @throws exception
     */
    public static function saveConfig(array $config)
    {
        foreach (self::$ConfigKeys as $defaultKey) {
            if (!array_key_exists($defaultKey, $config)) {
                throw new Exception("New config is missing the required {$defaultKey} key from it's array!");
            }
        }
        return AntYaml::saveFile(antConfigFile, $config);
    }
}
