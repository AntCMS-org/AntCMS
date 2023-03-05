<?php

namespace AntCMS;

use AntCMS\AntMarkdown;
use AntCMS\AntPages;
use AntCMS\AntConfig;

class AntCMS
{
    protected $antTwig;

    public function __construct()
    {
        $this->antTwig = new AntTwig();
    }

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

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $pageTemplate = $this->getPageLayout(null, $page);

        $params = [
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
            'AntCMSBody' => AntMarkdown::renderMarkdown($content['content']),
        ];
        $pageTemplate = $this->antTwig->renderWithTiwg($pageTemplate, $params);

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
    public static function getPageLayout(string $theme = null, string $currentPage = '')
    {
        $siteInfo = AntCMS::getSiteInfo();

        $pageTemplate = self::getThemeTemplate('default', $theme);
        $pageTemplate = str_replace('<!--AntCMS-Navigation-->', AntPages::generateNavigation(self::getThemeTemplate('nav', $theme), $currentPage), $pageTemplate);

        return $pageTemplate = str_replace('<!--AntCMS-SiteTitle-->', $siteInfo['siteTitle'], $pageTemplate);
    }

    /**
     * Render an exception page with the provided exception code.
     * 
     * @param string $exceptionCode The exception code to be displayed on the error page
     * @param int $httpCode The HTTP response code to return, 404 by default.
     * @param string $exceptionString An optional parameter to define a custom string to be displayed along side the exception. 
     * @return never 
     */
    public function renderException(string $exceptionCode, int $httpCode = 404, string $exceptionString = 'That request caused an exception to be thrown.')
    {
        $exceptionString .= " (Code {$exceptionCode})";
        $pageTemplate = self::getPageLayout();

        $params = [
            'AntCMSTitle' => 'An Error Ocurred',
            'AntCMSBody' => '<h1>An error ocurred</h1><p>' . $exceptionString . '</p>',
        ];
        try {
            $pageTemplate = $this->antTwig->renderWithTiwg($pageTemplate, $params);
        } catch (\Exception) {
            $pageTemplate = str_replace('{{ AntCMSTitle }}', $params['AntCMSTitle'], $pageTemplate);
            $pageTemplate = str_replace('{{ AntCMSBody | raw }} ', $params['AntCMSBody'], $pageTemplate);
        }

        http_response_code($httpCode);
        echo $pageTemplate;
        exit;
    }

    /** 
     * @return array<mixed>|false 
     */
    public function getPage(string $page)
    {
        $page = strtolower($page);
        $pagePath = AntTools::convertFunctionaltoFullpath($page);

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
     * @param string|null $theme 
     * @return string 
     */
    public static function getThemeTemplate(string $layout = 'default', string $theme = null)
    {
        $theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        if (strpos($layout, '_') !== false) {
            $layoutPrefix = explode('_', $layout)[0];
            $templatePath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Templates' . '/' . $layoutPrefix);
            $defaultTemplates = AntTools::repairFilePath(antThemePath . '/Default/Templates' . '/' . $layoutPrefix);
        } else {
            $templatePath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Templates');
            $defaultTemplates = AntTools::repairFilePath(antThemePath . '/Default/Templates');
        }

        try {
            $templates = AntTools::getFileList($templatePath, 'twig');
            if (in_array($layout . '.html.twig', $templates)) {
                $template = file_get_contents(AntTools::repairFilePath($templatePath . '/' . $layout . '.html.twig'));
            } else {
                $template = file_get_contents(AntTools::repairFilePath($defaultTemplates . '/' . $layout . '.html.twig'));
            }
        } catch (\Exception) {
        }

        if (empty($template)) {
            if ($layout == 'default') {
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
                        {{ AntCMSBody | raw }}
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
     * @return array<mixed> 
     */
    public static function getPageHeaders(string $pageContent)
    {
        $pageHeaders = [
            'title' => 'AntCMS',
            'author' => 'AntCMS',
            'description' => 'AntCMS',
            'keywords' => '',
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
            $pageHeaders['keywords'] = trim($matches[1] ?? '');
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

    public static function redirect(string $url)
    {
        $url = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . $url);
        header("Location: $url");
        exit;
    }
}
