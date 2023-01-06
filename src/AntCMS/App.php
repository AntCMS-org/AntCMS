<?php

namespace AntCMS;

use AntCMS\AntMarkdown;
use AntCMS\AntKeywords;

class AntCMS
{
    public function renderPage($page, $params = null)
    {
        $start_time = microtime(true);
        $content = $this->getPage($page);

        if (!$content || !is_array($content)) {
            $this->renderException("404");
        }

        $markdown = AntMarkdown::renderMarkdown($content['content']);
        $pageTemplate = $this->getThemeContent();

        $pageTemplate = str_replace('<!--AntCMS-Body-->', $markdown, $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Title-->', $content['title'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Description-->', $content['description'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Author-->', $content['author'], $pageTemplate);
        $pageTemplate = str_replace('<!--AntCMS-Keywords-->', $content['keywords'], $pageTemplate);

        $end_time = microtime(true);
        $elapsed_time = round($end_time - $start_time, 4);
        $pageTemplate = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' seconds to render the page.', $pageTemplate);

        echo $pageTemplate;
    }

    public function renderException($exceptionCode)
    {
        $content  = "# Error";
        $content .= "That request caused an $exceptionCode";
        echo AntMarkdown::renderMarkdown($content);
    }

    public function getPage($page)
    {
        $page = strtolower($page);
        $pagePath = AntDir . "/Content/$page.md";
        $AntKeywords = new AntKeywords();
        if (file_exists($pagePath)) {
            try {
                $pageContent = file_get_contents($pagePath);

                // Extract the AntCMS header using the regular expression
                preg_match('/--AntCMS--\n(?:Title: (.*)\n)?(?:Author: (.*)\n)?(?:Description: (.*)\n)?(?:Keywords: (.*)\n)?--AntCMS--\n/', $pageContent, $matches);

                // Remove the AntCMS section from the content
                $pageContent = preg_replace('/--AntCMS--.*--AntCMS--/s', '', $pageContent);

                // Extract the values from the $matches array and provide default values if the elements are missing
                $title = $matches[1] ?? 'AntCMS';
                $author = $matches[2] ?? 'AntCMS';
                $description = $matches[3] ?? 'AntCMS';
                $keywords = $matches[4] ?? $AntKeywords->generateKeywords($pageContent);

                $result = ['content' => $pageContent, 'title' => $title, 'author' => $author, 'description' => $description, 'keywords' => $keywords];
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
        $themePath = AntDir . "/Theme/default_layout.html";
        $themeContent = file_get_contents($themePath);

        if (!$themeContent) {
            $themeContent = '
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
}
