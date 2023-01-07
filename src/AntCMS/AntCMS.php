<?php

namespace AntCMS;

use AntCMS\AntMarkdown;
use AntCMS\AntKeywords;
use AntCMS\AntPages;
use AntCMS\AntConfig;

class AntCMS
{
    public function renderPage($page, $params = null)
    {
        $start_time = microtime(true);
        $content = $this->getPage($page);
        $siteInfo = AntCMS::getSiteInfo();
        $currentConfig = AntConfig::currentConfig();

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $markdown = AntMarkdown::renderMarkdown($content['content']);
        $theme = $this->getThemeContent();
        $pageTemplate = $theme['default_layout'];

        $pageTemplate = str_replace('<!--AntCMS-Description-->', $content['description'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Author-->', $content['author'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Keywords-->', $content['keywords'], $pageTemplate);

        $pageTemplate = str_replace('<!--AntCMS-Title-->', $content['title'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Navigation-->', AntPages::generateNavigation($theme['nav_layout']), $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $markdown, $pageTemplate);

        $pageTemplate = str_replace('<!--AntCMS-SiteTitle-->', $siteInfo['siteTitle'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-SiteLink-->', '//' . $currentConfig['baseURL'], $pageTemplate);

        $end_time = microtime(true);
        $elapsed_time = round($end_time - $start_time, 4);

        if ($currentConfig['debug']) {
            $pageTemplate = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' seconds to render the page. </p>', $pageTemplate);
        }

        echo $pageTemplate;
        exit;
    }

    public function renderException($exceptionCode)
    {
        $content  = "# Error";
        $content .= '<br>';
        $content .= "That request caused an exception code ($exceptionCode)";
        echo AntMarkdown::renderMarkdown($content);
        exit;
    }

    public function getPage($page)
    {
        $page = strtolower($page);
        $pagePath = AntDir . "/Content/$page";
        $pagePath = str_replace('//', '/', $pagePath);

        if (is_dir($pagePath)) {
            $pagePath = $pagePath . '/index.md';
        } else {
            $pagePath = (file_exists($pagePath)) ? $pagePath : $pagePath . '.md';
        }

        if (file_exists($pagePath)) {
            try {
                $pageContent = file_get_contents($pagePath);
                $pageHeaders = AntCMS::getPageHeaders($pageContent);
                // Remove the AntCMS section from the content
                $pageContent = preg_replace('/--AntCMS--.*--AntCMS--/s', '', $pageContent);
                $result = ['content' => $pageContent, 'title' => $pageHeaders['title'], 'author' => $pageHeaders['author'], 'description' => $pageHeaders['description'], 'keywords' => $pageHeaders['keywords']];
                return $result;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getThemeContent()
    {
        $currentConfig = AntConfig::currentConfig();
        $themePath = antThemePath . '/' . $currentConfig['activeTheme'];
        $templatePath = $themePath . '/' . 'Templates';
        $themeContent['default_layout'] = file_get_contents($templatePath . '/default_layout.html');
        $themeContent['nav_layout'] = file_get_contents($templatePath . '/nav_layout.html');

        if (!$themeContent['nav_layout']) {
            $themeContent['default_layout'] = '';
        }

        if (!$themeContent['default_layout']) {
            $themeContent['default_layout'] = '
            <!DOCTYPE html>
            <html>
                <head>
                    <title><!--AntCMS-Title--></title>
                    <meta name="description" content="<!--AntCMS-Description-->">
                    <meta name="author" content="<!--AntCMS-Author-->">
                    <meta name="keywords" content="<!--AntCMS-Keywords-->">
                </head>
                <body>
                    <!--AntCMS-Body-->
                </body>
            </html>';
        }

        return $themeContent;
    }

    public static function getPageHeaders($pageContent)
    {
        $AntKeywords = new AntKeywords();

        preg_match('/--AntCMS--.*--AntCMS--/s', $pageContent, $matches);
        // Remove the AntCMS section from the content
        $pageContent = preg_replace('/--AntCMS--.*--AntCMS--/s', '', $pageContent);
        $pageHeaders = [];

        if ($matches) {
            $header = $matches[0];

            preg_match('/Title: (.*)/', $header, $matches);
            $pageHeaders['title'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Author: (.*)/', $header, $matches);
            $pageHeaders['author'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Description: (.*)/', $header, $matches);
            $pageHeaders['description'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Keywords: (.*)/', $header, $matches);
            $pageHeaders['keywords'] = trim($matches[1] ?? $AntKeywords->generateKeywords($pageContent));
        } else {
            $pageHeaders = [
                'title' => 'AntCMS',
                'author' => 'AntCMS',
                'description' => 'AntCMS',
                'keywords' => trim($AntKeywords->generateKeywords($pageContent)),
            ];
        }
        return $pageHeaders;
    }

    public static function getSiteInfo()
    {
        $currentConfig = AntConfig::currentConfig();
        return $currentConfig['SiteInfo'];
    }
}
