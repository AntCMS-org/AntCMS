<?php

/**
 * Copyright 2025 AntCMS
 */

use AntCMS\PluginController;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testGetLoadedPlugins(): void
    {
        PluginController::init();
        $result = AntCMS\PluginController::getLoadedPlugins();
        $this->assertIsArray($result);
        $result = array_flip($result);
        $this->assertArrayHasKey('Robotstxt', $result);
        $this->assertArrayHasKey('Sitemap', $result);
        $this->assertArrayHasKey('System', $result);
    }
}
