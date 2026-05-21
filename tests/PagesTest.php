<?php

declare(strict_types=1);

/**
 * Copyright 2025 AntCMS
 */

use AntCMS\Pages;
use PHPUnit\Framework\TestCase;

class PagesTest extends TestCase
{
    public function testGetGenerateAndGetPages(): void
    {
        $result = Pages::getPages();
        $this->assertNotEmpty($result);
        $this->assertIsArray($result);
    }
}
