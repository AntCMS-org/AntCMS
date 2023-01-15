<?php

namespace AntCMS;

use AntCMS\AntMarkdown;
use AntCMS\AntKeywords;
use AntCMS\AntPages;
use AntCMS\AntConfig;

class AntCMS
{
    /**
     * Renders a page based on the provided page name.
     *
     * @param string $page The name of the page to be rendered
     * @return string The rendered HTML of the page
     */
    public function renderPage(string $page)
    {
        $start_time = microtime(true);
        $content = $this->getPage($page);
        $antTwig = new AntTwig;

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $markdown = AntMarkdown::renderMarkdown($content['content']);

        $pageTemplate = $this->getPageLayout(null, $page);

        $params = array(
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
        );
        $pageTemplate = str_replace('<!--AntCMS-Body-->', $markdown, $pageTemplate);
        $pageTemplate = $antTwig->renderWithTiwg($pageTemplate, $params);

        $end_time = microtime(true);
        $elapsed_time = round($end_time - $start_time, 4);

        if (AntConfig::currentConfig('debug')) {
            $pageTemplate = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' seconds to render the page. </p>', $pageTemplate);
        }

        return $pageTemplate;
    }

    /**
     * Returns the default layout of the active theme unless otherwise specified.
     * 
     * @param string|null $theme optional - the theme to get the page layout for.
     * @param string $currentPage optional - What page is the active page.
     * @return string the default page layout
     */
    public function getPageLayout(string $theme = null, string $currentPage = '')
    {
        $siteInfo = AntCMS::getSiteInfo();

        $pageTemplate = $this->getThemeTemplate('default_layout', $theme);
        $pageTemplate = str_replace('<!--AntCMS-Navigation-->', AntPages::generateNavigation($this->getThemeTemplate('nav_layout', $theme), $currentPage), $pageTemplate);

        $pageTemplate = str_replace('<!--AntCMS-SiteTitle-->', $siteInfo['siteTitle'], $pageTemplate);

        return str_replace('<!--AntCMS-SiteLink-->', '//' . AntConfig::currentConfig('baseURL'), $pageTemplate);
    }

    /**
     * Render an exception page with the provided exception code.
     * 
     * @param string $exceptionCode The exception code to be displayed on the error page
     * @return never 
     */
    public function renderException(string $exceptionCode)
    {
        $pageTemplate = $this->getPageLayout();

        $pageTemplate = str_replace('<!--AntCMS-Title-->', 'An error ocurred', $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Body-->', '<h1>An error ocurred</h1><p>That request caused an exception code (' . $exceptionCode . ')</p>', $pageTemplate);

        echo $pageTemplate;
        exit;
    }

    /**
     * @param string $page 
     * @return array<mixed>|false 
     */
    public function getPage(string $page)
    {
        $page = strtolower($page);
        $pagePath = AntDir . "/Content/$page";
        $pagePath = AntTools::repairFilePath($pagePath);

        if (is_dir($pagePath)) {
            $pagePath .= '/index.md';
        } else {
            $pagePath = (file_exists($pagePath)) ? $pagePath : $pagePath . '.md';
        }

        if (file_exists($pagePath)) {
            try {
                $pageContent = file_get_contents($pagePath);
                $pageHeaders = AntCMS::getPageHeaders($pageContent);
                // Remove the AntCMS section from the content
                $pageContent = preg_replace('/\A--AntCMS--.*?--AntCMS--/sm', '', $pageContent);
                return ['content' => $pageContent, 'title' => $pageHeaders['title'], 'author' => $pageHeaders['author'], 'description' => $pageHeaders['description'], 'keywords' => $pageHeaders['keywords']];
            } catch (\Exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string $layout 
     * @param string|null $theme 
     * @return string 
     */
    public function getThemeTemplate(string $layout = 'default_layout', string $theme = null)
    {
        $theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $templatePath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Templates');
        $defaultTemplates = AntTools::repairFilePath(antThemePath . '/Default/Templates');

        $templates = AntTools::getFileList($templatePath, 'twig');

        try {
            if (in_array($layout . '.html.twig', $templates)) {
                $template = file_get_contents(AntTools::repairFilePath($templatePath . '/' . $layout . '.html.twig'));
            } else {
                $template = file_get_contents(AntTools::repairFilePath($defaultTemplates . '/' . $layout . '.html.twig'));
            }
        } catch (\Exception) {
        }

        if (empty($template)) {
            if ($layout == 'default_layout') {
                $template = '
                <!DOCTYPE html>
                <html>
                    <head>
                        <title>{{ AntCMSTitle }}</title>
                        <meta name="description" content="{{ AntCMSDescription }}">
                        <meta name="author" content="{{ AntCMSAuthor }}">
                        <meta name="keywords" content="{{ AntCMSKeywords }}">
                    </head>
                    <body>
                        <p>AntCMS had an error when fetching the page template, please contact the site administrator.</p>
                        <!--AntCMS-Body-->
                    </body>
                </html>';
            } else {
                $template = '
                <h1>There was an error</h1>
                <p>AntCMS had an error when fetching the page template, please contact the site administrator.</p>';
            }
        }

        return $template;
    }

    /**
     * @param string $pageContent 
     * @return array<mixed> 
     */
    public static function getPageHeaders(string $pageContent)
    {
        $AntKeywords = new AntKeywords();
        $pageHeaders = [
            'title' => 'AntCMS',
            'author' => 'AntCMS',
            'description' => 'AntCMS',
            'keywords' => trim($AntKeywords->generateKeywords($pageContent)),
        ];

        // First get the AntCMS header and store it in the matches varible
        preg_match('/\A--AntCMS--.*?--AntCMS--/sm', $pageContent, $matches);

        if (isset($matches[0])) {
            $header = $matches[0];
            // Then remove it from the page content so it doesn't cause issues if we try to generate the keywords
            $pageContent = str_replace($header, '', $pageContent);

            preg_match('/Title: (.*)/', $header, $matches);
            $pageHeaders['title'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Author: (.*)/', $header, $matches);
            $pageHeaders['author'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Description: (.*)/', $header, $matches);
            $pageHeaders['description'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Keywords: (.*)/', $header, $matches);
            $keywods = $matches[1] ?? $AntKeywords->generateKeywords($pageContent);
            $pageHeaders['keywords'] = trim($keywods);
        }

        return $pageHeaders;
    }

    /**
     * @return mixed
     */
    public static function getSiteInfo()
    {
        return AntConfig::currentConfig('siteInfo');
    }

    /**
     * @param string $path 
     * @return void 
     */
    public function serveContent(string $path)
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
