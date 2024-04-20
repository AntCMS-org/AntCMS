<?php

use AntCMS\AntCMS;
use AntCMS\AntPages;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class CMSTest extends TestCase
{
    public function testgetSiteInfo(): void
    {
        $siteInfo = AntCMS::getSiteInfo();

        $this->assertIsArray($siteInfo);
        $this->assertArrayHasKey('siteTitle', $siteInfo);
        $this->assertEquals('AntCMS', $siteInfo['siteTitle']);
    }

    public function testRenderPage(): void
    {
        AntPages::generatePages();

        $antCMS = new AntCMS();
        $pagePath = '/index.md';
        $result = $antCMS->renderPage($pagePath);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    public function testGetPageLayout(): void
    {
        //We need to generate the Pages.yaml file so that the nav list can be generated.
        AntPages::generatePages();

        $antCMS = new AntCMS();
        $pageLayout = $antCMS->getPageLayout();

        $this->assertNotEmpty($pageLayout);
        $this->assertIsString($pageLayout);
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

        $this->assertEquals(false, $result);
        $this->assertIsBool($result);
    }

    public function testGetThemeTemplate(): void
    {
        $antCMS = new AntCMS();
        $result = $antCMS->getThemeTemplate();

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    public function testGetThemeTemplateFallback(): void
    {
        $antCMS = new AntCMS();
        $result = $antCMS->getThemeTemplate('atemplatethatjusdoesntexist');

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }
}
