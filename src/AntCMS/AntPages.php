<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;

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
            );
            $pageList[] = $currentPage;
        }
        AntYaml::saveFile(antPagesList, $pageList);
    }

    public static function getPages()
    {
    }
}
