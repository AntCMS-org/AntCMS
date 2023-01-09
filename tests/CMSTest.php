<?php

use AntCMS\AntCMS;
use AntCMS\AntPages;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class CMSTest extends TestCase
{
    public function testgetSiteInfo()
    {
        $siteInfo = AntCMS::getSiteInfo();

        $this->assertIsArray($siteInfo);
        $this->assertArrayHasKey('siteTitle', $siteInfo);
        $this->assertEquals('AntCMS', $siteInfo['siteTitle']);
    }

    /* Since this function echos the page and exists processing, we don't get the chance to test the return. Will revist.
    public function testRenderPage(){
        $antCMS = new AntCMS;
        $pagePath = '/index.md';
        $result = $antCMS->renderPage($pagePath);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }*/

    public function testGetPageLayout()
    {
        //We need to generate the pages.yaml file so that the nav list can be generated.
        AntPages::generatePages();

        $antCMS = new AntCMS;
        $result = $antCMS->getPageLayout();

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }

    public function testGetPage()
    {
        $antCMS = new AntCMS;
        $result = $antCMS->getPage('/index.md');

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('keywords', $result);
    }

    public function testGetPageFailed()
    {
        $antCMS = new AntCMS;
        $result = $antCMS->getPage('/thisdoesnotexist.md');

        $this->assertEquals(false, $result);
        $this->assertIsBool($result);
    }

    public function testGetThemeTemplate()
    {
        $antCMS = new AntCMS;
        $result = $antCMS->getThemeTemplate();

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }
}
