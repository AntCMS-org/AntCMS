<?php

namespace AntCMS;

use AntCMS\AntMarkdown;
use AntCMS\AntPages;
use AntCMS\AntConfig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AntCMS
{
    protected $antTwig;
    protected ?Response $response = null;
    protected ?Request $request = null;

    public function __construct()
    {
        $this->antTwig = new AntTwig();
    }

    public function SetResponse(?Response $response): void
    {
        $this->response = $response;
    }

    public function setRequest(?Request $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Renders a page based on the provided page name.
     *
     * @param string $page The name of the page to be rendered
     * @return string The rendered HTML of the page
     */
    public function renderPage(): Response
    {
        $page = $this->request->getUri()->getPath();

        $start_time = hrtime(true);
        $content = $this->getPage($page);
        $themeConfig = Self::getThemeConfig();

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $pageTemplate = $this->getPageLayout(null, $page, $content['template']);

        $params = [
            'AntCMSTitle' => $content['title'],
            'AntCMSDescription' => $content['description'],
            'AntCMSAuthor' => $content['author'],
            'AntCMSKeywords' => $content['keywords'],
            'AntCMSBody' => AntMarkdown::renderMarkdown($content['content']),
            'ThemeConfig' => $themeConfig['config'] ?? [],
        ];

        $pageTemplate = $this->antTwig->renderWithTiwg($pageTemplate, $params);
        $elapsed_time = (hrtime(true) - $start_time) / 1e+6;

        if (AntConfig::currentConfig('debug')) {
            $pageTemplate = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' milliseconds to render the page. </p>', $pageTemplate);
        }

        $response = $this->getResponse();
        $response->getBody()->write($pageTemplate);
        return $response;
    }

    /**
     * Returns the default layout of the active theme unless otherwise specified.
     * 
     * @param string|null $theme optional - the theme to get the page layout for.
     * @param string $currentPage optional - What page is the active page.
     * @return string the default page layout
     */
    public static function getPageLayout(string $theme = null, string $currentPage = '', string | null $template = null)
    {
        $layout = empty($template) ? 'default' : $template;
        $pageTemplate = self::getThemeTemplate($layout, $theme);
        return str_replace('<!--AntCMS-Navigation-->', AntPages::generateNavigation(self::getThemeTemplate('nav', $theme), $currentPage), $pageTemplate);
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
                return ['content' => $pageContent, 'title' => $pageHeaders['title'], 'author' => $pageHeaders['author'], 'description' => $pageHeaders['description'], 'keywords' => $pageHeaders['keywords'], 'template' => $pageHeaders['template']];
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
        $theme ??= AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . DIRECTORY_SEPARATOR . $theme)) {
            $theme = 'Default';
        }

        $basePath = AntTools::repairFilePath(antThemePath . DIRECTORY_SEPARATOR . $theme);

        if (strpos($layout, '_') !== false) {
            $layoutPrefix = explode('_', $layout)[0];
            $templatePath = $basePath . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $layoutPrefix;
            $defaultTemplates = AntTools::repairFilePath(antThemePath . '/Default/Templates' . '/' . $layoutPrefix);
        } else {
            $templatePath = $basePath . DIRECTORY_SEPARATOR . 'Templates';
            $defaultTemplates = AntTools::repairFilePath(antThemePath . '/Default/Templates');
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
        return AntConfig::currentConfig('siteInfo');
    }

    /** 
     * @return void 
     */
    public function serveContent(): Response
    {
        $path = $this->request->getUri();
        if (!file_exists($path)) {
            $this->renderException('404');
        } else {
            $response = $this->response->withHeader('Content-Type', mime_content_type($path) . '; charset=UTF-8');
            $response->getBody()->write(file_get_contents($path));
            return $response;
        }
    }

    public static function redirect(string $url)
    {
        $url = '//' . AntTools::repairURL(AntConfig::currentConfig('baseURL') . $url);
        header("Location: $url");
        exit;
    }

    public static function getThemeConfig(string|null $theme = null)
    {
        $theme = $theme ?? AntConfig::currentConfig('activeTheme');

        if (!is_dir(antThemePath . '/' . $theme)) {
            $theme = 'Default';
        }

        $configPath = AntTools::repairFilePath(antThemePath . '/' . $theme . '/' . 'Config.yaml');
        if (file_exists($configPath)) {
            $config = AntYaml::parseFile($configPath);
        }

        return $config ?? [];
    }
}
