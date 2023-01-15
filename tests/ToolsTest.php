<?php

use AntCMS\AntTools;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class ToolsTest extends TestCase
{
    public function testPathRepair()
    {
        $badPaths = array(
            "path/to/file",
            "path\\to\\file",
            "/path/to/file",
            "C:\\path\\to\\file",
            "~/path/to/file"
        );
        $expectedPaths = array(
            "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file",
            "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file", DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file",
            "C:" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file",
            "~" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "to" . DIRECTORY_SEPARATOR . "file"
        );


        foreach ($badPaths as $index => $badPath) {
            $goodPath = AntTools::repairFilePath($badPath);
            $this->assertEquals($expectedPaths[$index], $goodPath, "Expected '$expectedPaths[$index]' but got '{$goodPath}' for input '{$badPath}'");
        }
    }

    public function testGetFileList()
    {
        $basedir = dirname(__DIR__, 1);
        $srcdir = $basedir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Content';

        $result = AntTools::getFileList($srcdir);

        $this->assertNotEmpty($result);
    }

    public function testGetFileListWithExtension()
    {
        $basedir = dirname(__DIR__, 1);
        $srcdir = $basedir . DIRECTORY_SEPARATOR . 'src';

        $files = AntTools::getFileList($srcdir, 'md');

        foreach ($files as $file) {
            $this->assertEquals('md', pathinfo($file, PATHINFO_EXTENSION), "Expected file extension to be 'md', but got '" . pathinfo($file, PATHINFO_EXTENSION) . "' for file '{$file}'");
        }
    }
}
