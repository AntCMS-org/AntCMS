<?php

/**
 * Copyright 2026 AntCMS
 */

use AntCMS\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSaveConfigFailed(): void
    {
        $badConfig = [
            'cacheMode' => 'none',
        ];

        try {
            $result = Config::saveConfig($badConfig);
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
