<?php

namespace AntCMS;

use AntCMS\AntYaml;

class AntConfig
{
    public static function generateConfig()
    {
        $defaultOptions = array(
            'forceHTTPS' => true,
            'activeTheme' => 'default',
            'generateKeywords' => true,
            'enableCache' => true,
            'admin' =>  array(
                'password' => '',
                'username' => '',
            ),
            'debug' => true,
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
