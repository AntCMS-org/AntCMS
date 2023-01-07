<?php

namespace AntCMS;

use AntCMS\AntYaml;

class AntConfig
{
    public static function generateConfig()
    {
        $defaultOptions = array(
            'SiteInfo' => array(
                'siteTitle' => 'AntCMS',
            ),
            'forceHTTPS' => true,
            'activeTheme' => 'Default',
            'generateKeywords' => true,
            'enableCache' => true,
            'admin' =>  array(
                'password' => '',
                'username' => '',
            ),
            'debug' => true,
            'baseURL' => $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']),
        );

        AntYaml::saveFile(antConfigFile, $defaultOptions);
    }

    public static function currentConfig()
    {
        return AntYaml::parseFile(antConfigFile);
    }

    public static function saveConfig($config)
    {
        AntYaml::saveFile(antConfigFile, $config);
    }
}
