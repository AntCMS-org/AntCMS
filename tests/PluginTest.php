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
        $extensionList = [
            'Robotstxt',
            'Sitemap',
            'System',
        ];

        PluginController::init();
        $result = AntCMS\PluginController::getLoadedPlugins();
        $this->assertIsArray($result);
        $this->assertEquals($extensionList, $result);
    }
}
