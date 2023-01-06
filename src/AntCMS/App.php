<?php
namespace AntCMS;
use AntCMS\AntMarkdown;

class AntCMS {
    public function renderPage($page, $params = null){
        $content = $this->getPage($page);
        if(!$content){
            $this->renderException("404");
        } else {
            echo AntMarkdown::renderMarkdown($content);
        }
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
}
