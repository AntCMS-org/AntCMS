<?php

use AntCMS\AntConfig;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class ConfigTest extends TestCase
{
    public function testGetConfig()
    {
        $config = AntConfig::currentConfig();

        $expectedKeys = array(
            'siteInfo',
            'forceHTTPS',
            'activeTheme',
            'generateKeywords',
            'enableCache',
            'admin',
            'debug',
            'baseURL'
        );

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Expected key '$key' not found in config array");
        }
    }
}
