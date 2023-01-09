<?php

use AntCMS\AntMarkdown;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\Callback;

include_once 'Includes' . DIRECTORY_SEPARATOR . 'Include.php';

class MarkdownTest extends TestCase
{
    public function testCanRenderMarkdown()
    {
        $result = trim(AntMarkdown::renderMarkdown("# Test Content!"));
        $this->assertEquals('<h1>Test Content!</h1>', $result);
    }

    public function testMarkdownIsFast()
    {
        $markdown = file_get_contents(antContentPath . DIRECTORY_SEPARATOR . 'index.md');

        $start = microtime(true);
        AntMarkdown::renderMarkdown($markdown);
        $end = microtime(true);
        $time = $end - $start;

        $constraint = new Callback(function ($time) {
            return $time < 0.5;
        });

        $this->assertThat($time, $constraint, 'AntMarkdown::renderMarkdown took too long!');
    }

    /*public function testMarkdownCacheWorks()
    {
        $markdown = file_get_contents(antContentPath . DIRECTORY_SEPARATOR . 'index.md');

        $start = microtime(true);
        AntMarkdown::renderMarkdown($markdown);
        $end = microtime(true);
        $firstTime = $end - $start;

        $start = microtime(true);
        AntMarkdown::renderMarkdown($markdown);
        $end = microtime(true);
        $secondTime = $end - $start;

        $this->assertLessThan($secondTime, $firstTime, 'Cache didn\'t speed up rendering!');
    }*/
}
