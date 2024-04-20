<?php

use AntCMS\AntConfig;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class ConfigTest extends TestCase
{
    public function testGetConfig(): void
    {
        $config = AntConfig::currentConfig();

        $expectedKeys = ['siteInfo', 'forceHTTPS', 'activeTheme', 'cacheMode', 'debug', 'baseURL'];

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $config, "Expected key '{$expectedKey}' not found in config array");
        }
    }

    public function testSaveConfigFailed(): void
    {
        $Badconfig = [
            'cacheMode' => 'none',
        ];

        try {
            $result = AntConfig::saveConfig($Badconfig);
        } catch (Exception $exception) {
            $result = $exception;
        }

        $this->assertNotTrue($result);
    }

    public function testSaveConfigPassed(): void
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
