<?php

namespace AntCMS;

use Flight;

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

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $themeConfig = self::getThemeConfig();
        $params = [
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
            'markdown' => Markdown::parse($content['content'], $content['cacheKey']),
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

    public function serveContent(string $path): void
    {
        if (!file_exists($path)) {
            $this->renderException('404');
        } else {
            $asset_mime_type = Tools::getContentType($path);
            [$result, $encoding] = Tools::doAssetCompression($path);
            Flight::response()->header('Content-Type', $asset_mime_type);
            Flight::response()->header('Content-Encoding', $encoding);
            echo $result;
        }
    }

    public static function getThemeConfig(string|null $theme = null)
    {
        $theme ??= Config::get('activeTheme');

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
