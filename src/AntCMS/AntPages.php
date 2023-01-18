<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;
use AntCMS\AntConfig;
use AntCMS\AntCache;
use AntCMS\AntTools;
use AntCMS\AntTwig;

class AntPages
{
    /** @return void  */
    public static function generatePages()
    {
        $pages = AntTools::getFileList(antContentPath, 'md', true);
        $pageList = array();

        foreach ($pages as $page) {
            $page = AntTools::repairFilePath($page);
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

    /** @return array<mixed>  */
    public static function getPages()
    {
        return AntYaml::parseFile(antPagesList);
    }

    /**
     * @param string $navTemplate
     * @param string $currentPage optional - What page is the active page. Used for highlighting the active page in the navbar
     * @return string 
     */
    public static function generateNavigation(string $navTemplate = '', string $currentPage = '')
    {
        $pages = AntPages::getPages();
        $antCache = new AntCache;

        $theme = AntConfig::currentConfig('activeTheme');
        $cacheKey = $antCache->createCacheKey(json_encode($pages), $theme . $currentPage);

        if ($antCache->isCached($cacheKey)) {
            $cachedContent = $antCache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $currentPage = strtolower($currentPage);
        if (str_ends_with($currentPage, '/')) {
            $currentPage .= 'index.md';
        }

        $baseURL = AntConfig::currentConfig('baseURL');
        foreach ($pages as $key => $page) {
            $url = "//" . AntTools::repairURL($baseURL . $page['functionalPagePath']);
            $pages[$key]['url'] = $url;
            $pages[$key]['active'] = $currentPage === strtolower($page['functionalPagePath']);

            //Remove pages that are hidden from the nav from the array before sending it to twig. 
            if (!(bool)$page['showInNav']) {
                unset($pages[$key]);
            }
        }

        $antTwig = new AntTwig;
        $navHTML = $antTwig->renderWithTiwg($navTemplate, array('pages' => $pages));

        $antCache->setCache($cacheKey, $navHTML);
        return $navHTML;
    }
}
