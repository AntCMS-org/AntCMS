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
            'enableCache',
            'debug',
            'baseURL'
        );

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $config, "Expected key '{$expectedKey}' not found in config array");
        }
    }

    public function testSaveConfigFailed()
    {
        $Badconfig = [
            'enableCache' => true,
        ];

        try {
            $result = AntConfig::saveConfig($Badconfig);
        } catch (Exception $exception) {
            $result = $exception;
        }

        $this->assertNotTrue($result);
    }

    public function testSaveConfigPassed()
    {
        $currentConfig = AntConfig::currentConfig();

        try {
            $result = AntConfig::saveConfig($currentConfig);
        } catch (Exception $exception) {
            $result = $exception;
        }

        $this->assertTrue($result);
    }
}
