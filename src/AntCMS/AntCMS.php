<?php

namespace AntCMS;

use AntCMS\Markdown;
use AntCMS\Pages;
use AntCMS\Config;
use Flight;
use HostByBelle\CompressionBuffer;

class AntCMS
{
    protected Cache $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    /**
     * Renders a page based on the provided page name.
     *
     * @param string $page The name of the page to be rendered
     * @return string The rendered HTML of the page
     */
    public function renderPage(string $page): string
    {
        $content = $this->getPage($page);
        $themeConfig = self::getThemeConfig();

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $params = [
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
            'markdown' => Markdown::renderMarkdown($content['content'], $content['cacheKey']),
            'ThemeConfig' => $themeConfig['config'] ?? [],
            'pages' => Pages::getNavList($page),
        ];

        if (Twig::templateExists($page)) {
            return Twig::render($page, $params);
        } else {
            return Twig::render('markdown.html.twig', $params);
        }
    }

    /**
     * Render an exception page with the provided exception code.
     *
     * @param string $exceptionCode The exception code to be displayed on the error page
     * @param int $httpCode The HTTP response code to return, 404 by default.
     * @param string $exceptionString An optional parameter to define a custom string to be displayed along side the exception.
     * @return never
     */
    public function renderException(string $exceptionCode, int $httpCode = 404, string $exceptionString = 'That request caused an exception to be thrown.'): void
    {
        $exceptionString .= " (Code {$exceptionCode})";

        $params = [
            'AntCMSTitle' => 'An Error Ocurred',
            'message' => $exceptionString,
            'pages' => Pages::getNavList(),
        ];

        $page = Twig::render('error.html.twig', $params);

        Flight::halt($httpCode, $page);
        exit;
    }

    /**
     * @return array<mixed>|false
     */
    public function getPage(string $page): array|false
    {
        $page = strtolower($page);
        $pagePath = Tools::convertFunctionaltoFullpath($page);

        if (file_exists($pagePath)) {
            try {
                $pageContent = file_get_contents($pagePath);
                $pageHeaders = AntCMS::getPageHeaders($pageContent);
                // Remove the AntCMS section from the content
                $pageContent = preg_replace('/\A--AntCMS--.*?--AntCMS--/sm', '', $pageContent);
                return [
                    'content' => $pageContent,
                    'title' => $pageHeaders['title'],
                    'author' => $pageHeaders['author'],
                    'description' => $pageHeaders['description'],
                    'keywords' => $pageHeaders['keywords'],
                    'template' => $pageHeaders['template'],
                    'lastMod' => filemtime($pagePath),
                    'cacheKey' => $this->cache->createCacheKeyFile($pagePath, 'content'),
                ];
            } catch (\Exception) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param string|null $theme
     */
    public static function getThemeTemplate(string $layout = 'default', string $theme = null): string
    {
        $theme ??= Config::currentConfig('activeTheme');

        if (!is_dir(antThemePath . DIRECTORY_SEPARATOR . $theme)) {
            $theme = 'Default';
        }

        $basePath = Tools::repairFilePath(antThemePath . DIRECTORY_SEPARATOR . $theme);

        if (str_contains($layout, '_')) {
            $layoutPrefix = explode('_', $layout)[0];
            $templatePath = $basePath . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $layoutPrefix;
            $defaultTemplates = Tools::repairFilePath(antThemePath . '/Default/Templates' . '/' . $layoutPrefix);
        } else {
            $templatePath = $basePath . DIRECTORY_SEPARATOR . 'Templates';
            $defaultTemplates = Tools::repairFilePath(antThemePath . '/Default/Templates');
        }

        try {
            $template = @file_get_contents($templatePath . DIRECTORY_SEPARATOR . $layout . '.html.twig');
            if (empty($template)) {
                $template = file_get_contents($defaultTemplates . DIRECTORY_SEPARATOR . $layout . '.html.twig');
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
    public static function getPageHeaders(string $pageContent): array
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

            preg_match('/Title: (.*)/', $header, $matches);
            $pageHeaders['title'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Author: (.*)/', $header, $matches);
            $pageHeaders['author'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Description: (.*)/', $header, $matches);
            $pageHeaders['description'] = trim($matches[1] ?? 'AntCMS');

            preg_match('/Keywords: (.*)/', $header, $matches);
            $pageHeaders['keywords'] = trim($matches[1] ?? '');

            preg_match('/Template: (.*)/', $header, $matches);
            $pageHeaders['template'] = trim($matches[1] ?? '');
        }

        return $pageHeaders;
    }

    /**
     * @return mixed
     */
    public static function getSiteInfo()
    {
        return Config::currentConfig('siteInfo');
    }

    public function serveContent(string $path): void
    {
        if (!file_exists($path)) {
            $this->renderException('404');
        } else {
            $asset_mime_type = mime_content_type($path);
            header('Content-Type: ' . $asset_mime_type);
            readfile($path);
        }
        CompressionBuffer::disable();
        Flight::halt(200);
    }

    public static function getThemeConfig(string|null $theme = null)
    {
        $theme ??= Config::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $configPath = Tools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Config.yaml');
        if (file_exists($configPath)) {
            $config = AntYaml::parseFile($configPath);
        }

        return $config ?? [];
    }
}
