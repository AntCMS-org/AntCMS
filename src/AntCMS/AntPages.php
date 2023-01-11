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
     * @return string 
     */
    public static function generateNavigation(string $navTemplate = '')
    {
        $pages = AntPages::getPages();
        $cache = new AntCache;

        $theme = AntConfig::currentConfig('activeTheme');
        $cacheKey = $cache->createCacheKey(json_encode($pages), $theme);

        if ($cache->isCached($cacheKey)) {
            $cachedContent = $cache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $baseURL = AntConfig::currentConfig('baseURL');
        foreach ($pages as $key => $page) {
            $url = "//" . AntTools::repairURL($baseURL . $page['functionalPagePath']);
            $pages[$key]['url'] = $url;
        }

        $antTwig = new AntTwig;
        $navHTML = $antTwig->renderWithTiwg($navTemplate, array('pages' =>$pages));

        $cache->setCache($cacheKey, $navHTML);
        return $navHTML;
    }
}
