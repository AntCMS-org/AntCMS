<?php
namespace AntCMS;
use AntCMS\AntMarkdown;

class AntCMS {
    public function renderPage($page, $params = null){
        $start_time = microtime(true);
        $content = $this->getPage($page);

        if(!$content){
            $this->renderException("404");
        }

        $markdown = AntMarkdown::renderMarkdown($content);
        $page = $this->getThemeContent();

        $page = str_replace('<!--AntCMS-Body-->', $markdown, $page);
        $page = str_replace('<!--AntCMS-Title-->', 'AntCMS', $page);

        $end_time = microtime(true);
        $elapsed_time = round($end_time - $start_time, 4);
        $page = str_replace('<!--AntCMS-Debug-->', '<p>Took ' . $elapsed_time . ' seconds to render the page.', $page);

        echo $page;
    }

    public function renderException($exceptionCode){
        $content  = "# Error";
        $content .= "That request caused an $exceptionCode";
        echo AntMarkdown::renderMarkdown($content);
    }

    public function getPage($page){
        $page = strtolower($page);
        $pagePath = appDir . "/Content/$page.md";
        if(file_exists($pagePath)){
            try {
                $pageContents = file_get_contents($pagePath);
                return $pageContents;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getThemeContent(){
        $themePath = appDir . "/Theme/default_layout.html";
        $themeContent = file_get_contents($themePath);

        if(!$themeContent){
            $themeContent = '
            <!DOCTYPE html>
            <html>
                <head>
                    <title><!--AntCMS-Title--></title>
                </head>
                <body>
                    <!--AntCMS-Body-->
                </body>
            </html>';
        }

        return $themeContent;
    }
}
