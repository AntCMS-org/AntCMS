<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;
use AntCMS\AntConfig;

class AntPages
{
    public static function generatePages()
    {
        $dir = new \RecursiveDirectoryIterator(antContentPath);
        $iterator = new \RecursiveIteratorIterator($dir);
        $pages = array();
        $pageList = array();
        foreach ($iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == "md") {
                $pages[] = $file->getPathname();
            }
        }

        foreach ($pages as $page) {
            $pageContent = file_get_contents($page);
            $pageHeader = AntCMS::getPageHeaders($pageContent);
            $pageFunctionalPath = str_replace(antContentPath, "", $page);
            $currentPage = array(
                'pageTitle' => $pageHeader['title'],
                'fullPagePath' => $page,
                'functionalPagePath' => $pageFunctionalPath,
                'showInNav' => true,
            );
            $pageList[] = $currentPage;
        }
        AntYaml::saveFile(antPagesList, $pageList);
    }

    public static function getPages()
    {
        return AntYaml::parseFile(antPagesList);
    }

    public static function generateNavigation($navTemplate = '')
    {
        $currentConfig = AntConfig::currentConfig();
        $baseURL = $currentConfig['baseURL'];
        $navHTML = '';
        foreach (AntPages::getPages() as $page) {
            if(!$page['showInNav']){
                continue;
            }
            $url = "//" . str_replace('//', '/',$baseURL . $page['functionalPagePath']);
            $navEntry = str_replace('<!--AntCMS-PageLink-->', $url, $navTemplate);
            $navEntry = str_replace('<!--AntCMS-PageTitle-->', $page['pageTitle'], $navEntry);
            $navHTML .= $navEntry;
        }
        return $navHTML;
    }
}
