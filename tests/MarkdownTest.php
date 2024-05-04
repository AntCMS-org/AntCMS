<?php

use AntCMS\Markdown;
use AntCMS\Config;
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

        //Ensure cache is enabled
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


    /* PHP's file modified time cache is causing issues. I should look at this later
    public function testMarkdownCacheWorks(): void
    {
        $markdown = file_get_contents(PATH_CONTENT . DIRECTORY_SEPARATOR . 'index.md');
        $currentConfig = Config::currentConfig();

        //Disable cache
        $currentConfig['cacheMode'] = 'none';
        Config::saveConfig($currentConfig);

        $totalTime = 0;
        for ($i = 0; $i < 10; ++$i) {
            $start = microtime(true);
            Markdown::renderMarkdown($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $withoutCache = $totalTime / 10;

        //Enable cache
        $currentConfig['cacheMode'] = 'auto';
        Config::saveConfig($currentConfig);

        $totalTime = 0;
        for ($i = 0; $i < 10; ++$i) {
            $start = microtime(true);
            Markdown::renderMarkdown($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $withCache = $totalTime / 10;

        echo "\n Markdown rendering speed with cache: {$withCache} VS without: {$withoutCache} \n\n";
        $this->assertLessThan($withoutCache, $withCache, "Cache didn't speed up rendering!");
    }*/
}
