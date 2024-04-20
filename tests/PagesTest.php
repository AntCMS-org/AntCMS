<?php

use AntCMS\Pages;
use AntCMS\AntCMS;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class PagesTest extends TestCase
{
    public function testGetGenerateAndGetPages(): void
    {
        Pages::generatePages();
        $result = Pages::getPages();

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }

    public function testGetNavigation(): void
    {
        $antCMS = new AntCMS();
        $pageTemplate = $antCMS->getThemeTemplate();
        $navLayout = $antCMS->getThemeTemplate('nav');

        $result = Pages::generateNavigation($navLayout, $pageTemplate);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }
}
