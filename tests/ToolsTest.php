<?php

/**
 * Copyright 2025 AntCMS
 */

use AntCMS\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    private string $testAsset = PATH_THEMES . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'TinyZoom.js';

    public function testUrlRepair(): void
    {
        $badUrls = ["example.com\path", "example.com/path/", "example.com//path", "example.com/path/to//file", "example.com\path\\to\\file", "example.com\path\\to\\file?download=yes"];
        $expectedUrls = ["example.com/path", "example.com/path/", "example.com/path", "example.com/path/to/file", "example.com/path/to/file", "example.com/path/to/file?download=yes"];


        foreach ($badUrls as $index => $badurl) {
            $goodUrl = Tools::repairURL($badurl);
            $this->assertEquals($expectedUrls[$index], $goodUrl, "Expected '$expectedUrls[$index]' but got '{$goodUrl}' for input '{$badurl}'");
        }
    }

    public function testGetContentType(): void
    {
        $result = Tools::getContentType($this->testAsset);
        $this->assertIsString($result);
        $this->assertEquals('application/javascript', $result);
    }

    /*
    public function testGetAssetCacheKey()
    {
        $result = Tools::getAssetCacheKey($this->testAsset);
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
    */
}
