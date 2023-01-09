<?php

use AntCMS\AntMarkdown;
use AntCMS\AntConfig;
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
        $totalTime = 0;

        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            AntMarkdown::renderMarkdown($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $averageTime = $totalTime / 10;

        $constraint = new Callback(function ($averageTime) {
            return $averageTime < 0.015;
        });

        $this->assertThat($averageTime, $constraint, 'AntMarkdown::renderMarkdown took too long on average!');
    }


    public function testMarkdownCacheWorks()
    {
        $markdown = file_get_contents(antContentPath . DIRECTORY_SEPARATOR . 'index.md');
        $currentConfig = AntConfig::currentConfig();

        //Disable cache
        $currentConfig['enableCache'] = false;
        AntConfig::saveConfig($currentConfig);

        $totalTime = 0;
        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            AntMarkdown::renderMarkdown($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $withoutCache = $totalTime / 10;

        //Enable cache
        $currentConfig['enableCache'] = true;
        AntConfig::saveConfig($currentConfig);

        $totalTime = 0;
        for ($i = 0; $i < 10; $i++) {
            $start = microtime(true);
            AntMarkdown::renderMarkdown($markdown);
            $end = microtime(true);
            $totalTime += $end - $start;
        }

        $withCache = $totalTime / 10;
        
        echo "\n Markdown rendering speed with cache: $withCache VS without: $withoutCache \n\n";
        $this->assertLessThan($withoutCache, $withCache, 'Cache didn\'t speed up rendering!');
    }
}
