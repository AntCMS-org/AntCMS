<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;
use AntCMS\AntConfig;
use AntCMS\AntCache;
use AntCMS\AntTools;

class AntPages
{
    public static function generatePages()
    {
        $pages = AntTools::getFileList(antContentPath, 'md', true);

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
        $pages = AntPages::getPages();
        $cache = new AntCache;

        $theme = $currentConfig['activeTheme'];
        $cacheKey = $cache->createCacheKey(json_encode($pages), $theme);

        if ($cache->isCached($cacheKey)) {
            $cachedContent = $cache->getCache($cacheKey);

            if ($cachedContent !== false && !empty($cachedContent)) {
                return $cachedContent;
            }
        }

        $navHTML = '';
        $baseURL = $currentConfig['baseURL'];
        foreach ($pages as $page) {
            if (!$page['showInNav']) {
                continue;
            }
            $url = "//" . str_replace('//', '/', $baseURL . $page['functionalPagePath']);
            $navEntry = str_replace('<!--AntCMS-PageLink-->', $url, $navTemplate);
            $navEntry = str_replace('<!--AntCMS-PageTitle-->', $page['pageTitle'], $navEntry);
            $navHTML .= $navEntry;
        }
        $cache->setCache($cacheKey, $navHTML);
        return $navHTML;
    }
}
