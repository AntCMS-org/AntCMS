<?php

use AntCMS\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    private string $testAsset = PATH_THEMES . DIRECTORY_SEPARATOR . 'Default' . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'TinyZoom.js';
    public function testPathRepair(): void
    {
        $badPaths = ["path/to/file", "path\\to\\file", "/path/to/file", "C:\\path\\to\\file", "~/path/to/file"];
        $expectedPaths = ["path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file", "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file", DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file", "C:" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file", "~" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file"];


        foreach ($badPaths as $index => $badPath) {
            $goodPath = Tools::repairFilePath($badPath);
            $this->assertEquals($expectedPaths[$index], $goodPath, "Expected '$expectedPaths[$index]' but got '{$goodPath}' for input '{$badPath}'");
        }
    }

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
