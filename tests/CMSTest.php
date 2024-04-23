<?php

use AntCMS\AntCMS;
use AntCMS\Pages;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class CMSTest extends TestCase
{
    public function testRenderPage(): void
    {
        Pages::generatePages();

        $antCMS = new AntCMS();
        $pagePath = '/index.md';
        $result = $antCMS->renderPage($pagePath);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    public function testGetPage(): void
    {
        $antCMS = new AntCMS();
        $result = $antCMS->getPage('/index.md');

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('keywords', $result);
    }

    public function testGetPageFailed(): void
    {
        $antCMS = new AntCMS();
        $result = $antCMS->getPage('/thisdoesnotexist.md');

        $this->assertEquals([], $result);
        $this->assertIsArray($result);
    }
}
