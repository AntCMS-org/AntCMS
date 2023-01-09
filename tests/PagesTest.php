<?php

use AntCMS\AntPages;
use AntCMS\AntCMS;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class PagesTest extends TestCase
{
    public function testGetGenerateAndGetPages()
    {
        AntPages::generatePages();
        $result = AntPages::getPages();

        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }

    public function testGetNavigation(){
        $antCMS = new AntCMS;
        $pageTemplate = $antCMS->getThemeTemplate();
        $navLayout = $antCMS->getThemeTemplate('nav_layout');

        $result = AntPages::generateNavigation($navLayout, $pageTemplate);

        $this->assertNotEmpty($result);
        $this->assertIsString($result);
    }
}
