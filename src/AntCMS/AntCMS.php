<?php

namespace AntCMS;

use Flight;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class AntCMS
{
    private readonly Filesystem $filesystem;
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Renders a page based on the provided page name.
     *
     * @param string $page The name of the page to be rendered
     * @return string The rendered HTML of the page
     */
    public function renderPage(?string $page = null): string
    {
        $page ??= Tools::getUri();
        $content = $this->getPage($page);

        if ($content === []) {
            $this->renderException("404");
        }

        HookController::fire('onAfterContentHit', ['contentUri' => $page]);

        $themeConfig = self::getThemeConfig();
        $params = [
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
            'markdown' => Markdown::parse($content['content'], $content['cacheKey']),
            'themeConfig' => $themeConfig['config'] ?? [],
            'pages' => Pages::getPages($page),
        ];

        if (Twig::templateExists($page)) {
            return Twig::render($page, $params);
        }
        return Twig::render('markdown.html.twig', $params);
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
            'pages' => Pages::getPages(),
        ];

        $page = Twig::render('error.html.twig', $params);

        Flight::halt($httpCode, $page);
        exit;
    }

    /**
     * @return array<mixed>
     */
    public function getPage(string $page): array
    {
        $page = strtolower($page);
        $pagePath = Tools::convertFunctionaltoFullpath($page);

        if ($this->filesystem->exists($pagePath)) {
            try {
                $pageContent = $this->filesystem->readFile($pagePath);
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
                    'cacheKey' => Cache::createCacheKeyFile($pagePath, 'content'),
                ];
            } catch (\Exception) {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * This method searches for and extracts the AntCMS header section from the provided page content.
     * It then uses regular expressions to extract the title, author, description, keywords, and template from the header.
     *
     * @param string $pageContent The page content to search for headers
     * @return array<mixed> The extracted page headers
     */
    public static function getPageHeaders(string $pageContent): array
    {
        $pageHeaders = [
            'title' => 'AntCMS',
            'author' => 'AntCMS',
            'description' => 'AntCMS',
            'keywords' => '',
        ];

        // First get the AntCMS header and store it in the matches variable
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

            preg_match('/NavItem: (.*)/', $header, $matches);
            $pageHeaders['NavItem'] = trim($matches[1] ?? '');
        }

        return $pageHeaders;
    }

    public function serveContent(?string $path = null): void
    {
        $path ??= '.' . Tools::getUri();
        if (str_starts_with($path, './assets/')) {
            $themeAssets = Path::normalize(PATH_CURRENT_THEME . '/Assets/');
            $path = $themeAssets . substr($path, 8);
        }

        if (!Path::isLocal($path)) {
            $this->renderException('Forbidden', 403, 'forbidden');
        }

        $path = Path::makeAbsolute($path, PATH_ROOT);

        if (!$this->filesystem->exists($path)) {
            $this->renderException('404');
        } else {
            // Needed info for cache handling
            $key = Tools::getAssetCacheKey($path);
            $lastMod = filemtime($path);
            $encoding = Tools::getExpectedEncoding($path);
            $asset_mime_type = Tools::getContentType($path);

            // Send the needed headers for the content encoding
            Flight::response()->header('Content-Type', $asset_mime_type);
            Flight::response()->header('Content-Encoding', $encoding);
            Flight::response()->header('Vary', 'Accept-Encoding');
            Flight::response()->header('Cache-Control', 'public, max-age=0 must-revalidate');

            // Send an ETag for client-side caching except on Caddy where it inexplicably breaks everything
            if (!str_contains($_SERVER['SERVER_SOFTWARE'] ?? '', 'Caddy')) {
                // Flight's etag implimentation is broken as it clears our headers
                $existingEtag = Flight::request()->getHeader('If-None-Match');
                if ($key === $existingEtag) {
                    Flight::response()->clearBody();
                    Flight::halt(304);
                }
            }

            if ($lastMod) {
                Flight::lastModified($lastMod);
            }

            echo Tools::doAssetCompression($path);
        }
    }

    /**
     * @return mixed[]
     */
    public static function getThemeConfig(string|null $theme = null): array
    {
        if ($theme === null) {
            $configPath = path::normalize(PATH_CURRENT_THEME . 'Config.yaml');
            if (file_exists($configPath)) {
                return AntYaml::parseFile($configPath);
            }
        } else {
            if (!is_dir(path::normalize(PATH_THEMES . '/' . $theme))) {
                $theme = 'Default';
            }

            $configPath = path::normalize(PATH_THEMES . '/' . $theme . '/' . 'Config.yaml');
            if (file_exists($configPath)) {
                return AntYaml::parseFile($configPath);
            }
        }

        return [];
    }
}
