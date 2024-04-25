<?php

use AntCMS\Pages;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class PagesTest extends TestCase
{
    public function testGetGenerateAndGetPages(): void
    {
        $result = Pages::getPages();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }
}
