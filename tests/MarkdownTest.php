<?php

use AntCMS\AntMarkdown;
use PHPUnit\Framework\TestCase;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class MarkdownTest extends TestCase
{
    public function testMarkdownContent()
    {
        $result = trim(AntMarkdown::renderMarkdown("# Test Content!"));
        $this->assertEquals('<h1>Test Content!</h1>', $result);
    }
}
