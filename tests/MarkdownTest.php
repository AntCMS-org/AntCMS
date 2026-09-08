<?php

/**
 * Copyright 2026 AntCMS
 */

use AntCMS\Config;
use AntCMS\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownTest extends TestCase
{
    public function testCanRenderMarkdown(): void
    {
        $result = trim(Markdown::parse("# Test Content!"));
        $this->assertEquals('<h1>Test Content!</h1>', $result);
    }

    public function testMarkdownIsFast(): void
    {
        $markdown = file_get_contents(PATH_CONTENT . DIRECTORY_SEPARATOR . 'index.md');
        $totalTime = 0;
        $currentConfig = Config::get();

        // Ensure cache is enabled
        $currentConfig['cacheMode'] = 'auto';
        Config::saveConfig($currentConfig);

        for ($i = 0; $i < 10; ++$i) {
            $start = microtime(true);
            Markdown::parse($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $averageTime = $totalTime / 10;
        $this->assertLessThan(0.015, $averageTime, 'AntMarkdown::renderMarkdown took too long on average!');
    }
}
