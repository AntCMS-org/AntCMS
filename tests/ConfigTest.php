<?php

use AntCMS\Config;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class ConfigTest extends TestCase
{
    public function testSaveConfigFailed(): void
    {
        $Badconfig = [
            'cacheMode' => 'none',
        ];

        try {
            $result = Config::saveConfig($Badconfig);
        } catch (Exception $exception) {
            $result = $exception;
        }

        $this->assertNotTrue($result);
    }

    public function testSaveConfigPassed(): void
    {
        $currentConfig = Config::get();

        try {
            $result = Config::saveConfig($currentConfig);
        } catch (Exception $exception) {
            $result = $exception;
        }

        $this->assertTrue($result);
    }
}
