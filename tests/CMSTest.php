<?php

/**
 * Copyright 2025 AntCMS
 */

use AntCMS\AntCMS;
use PHPUnit\Framework\TestCase;

class CMSTest extends TestCase
{
    public function testRenderPage(): void
    {
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
