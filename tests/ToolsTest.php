<?php

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

    public function testGetFileList(): void
    {
        $basedir = dirname(__DIR__, 1);
        $srcdir = $basedir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Content';

        $result = Tools::getFileList($srcdir);

        $this->assertNotEmpty($result);
    }

    public function testGetFileListWithExtension(): void
    {
        $basedir = dirname(__DIR__, 1);
        $srcdir = $basedir . DIRECTORY_SEPARATOR . 'src';

        $files = Tools::getFileList($srcdir, 'md');

        foreach ($files as $file) {
            $this->assertEquals('md', pathinfo($file, PATHINFO_EXTENSION), "Expected file extension to be 'md', but got '" . pathinfo($file, PATHINFO_EXTENSION) . "' for file '{$file}'");
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
