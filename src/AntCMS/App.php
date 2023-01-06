<?php
use Michelf\Markdown;

class AntCMS {
    public function renderPage($page, $params = null){
        $content = $this->getPage($page);
        if(!$content){
            $this->renderException("404");
        } else {
            echo Markdown::defaultTransform($content);
        }
    }

    public function renderException($exceptionCode){
        $content  = "# Error";
        $content .= "That request caused an $exceptionCode";
        echo Markdown::defaultTransform($content);
    }

    public function getPage($page){
        $page = strtolower($page);
        $pagePath = appDir . "/Content/$page.md";
        if(file_exists($pagePath)){
            try {
                $pageContents = file_get_contents($pagePath);
                return $pageContents;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
    
}
