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
        $currentConfig = AntConfig::currentConfig();

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $markdown = AntMarkdown::renderMarkdown($content['content']);

        $pageTemplate = $this->getPageLayout();

        $pageTemplate = str_replace('<!--AntCMS-Description-->', $content['description'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Author-->', $content['author'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Keywords-->', $content['keywords'], $pageTemplate);

        $pageTemplate = str_replace('<!--AntCMS-Title-->', $content['title'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $markdown, $pageTemplate);

        $end_time = microtime(true);
        $elapsed_time = round($end_time - $start_time, 4);

        if ($currentConfig['debug']) {
            $pageTemplate = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' seconds to render the page. </p>', $pageTemplate);
        }

        echo $pageTemplate;
        exit;
    }

    public function getPageLayout($theme = null)
    {
        $siteInfo = AntCMS::getSiteInfo();
        $currentConfig = AntConfig::currentConfig();

        $pageTemplate = $this->getThemeTemplate('default_layout', $theme);
        $pageTemplate = str_replace('<!--AntCMS-Navigation-->', AntPages::generateNavigation($this->getThemeTemplate('nav_layout', $theme)), $pageTemplate);

        $pageTemplate = str_replace('<!--AntCMS-SiteTitle-->', $siteInfo['siteTitle'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-SiteLink-->', '//' . $currentConfig['baseURL'], $pageTemplate);

        return $pageTemplate;
    }

    public function renderException($exceptionCode)
    {
        $pageTemplate = $this->getPageLayout();

        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'An error ocurred', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', '<h1>An error ocurred</h1><p>That request caused an exception code (' . $exceptionCode . ')</p>', $pageTemplate);

        echo $pageTemplate;
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

    public function getThemeTemplate($layout = 'default_layout', $theme = null)
    {
        $currentConfig = AntConfig::currentConfig();
        $theme = $theme ?? $currentConfig['activeTheme'];

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $templatePath = antThemePath . '/' . $theme . '/' . 'Templates';
        $defaultTemplates = antThemePath . '/Default/Templates';

        $templates = AntTools::getFileList($templatePath, 'html');

        if (in_array($layout . '.html', $templates)) {
            $template = file_get_contents($templatePath . '/' . $layout . '.html');
        } else {
            $template = file_get_contents($defaultTemplates . '/' . $layout . '.html');
        }

        if ($layout == 'default_layout' && !$template) {
            $template = '
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

        return $template;
    }

    public static function getPageHeaders($pageContent)
    {
        $AntKeywords = new AntKeywords();

        // First get the AntCMS header and store it in the matches varible
        preg_match('/--AntCMS--.*--AntCMS--/s', $pageContent, $matches);

        // Then remove it from the page content so it doesn't cause issues if we try to generate the keywords
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

    public function serveContent($path)
    {
        if (!file_exists($path)) {
            $this->renderException('404');
        } else {
            $asset_mime_type = mime_content_type($path);
            header('Content-Type: ' . $asset_mime_type);
            readfile($path);
        }
    }
}
