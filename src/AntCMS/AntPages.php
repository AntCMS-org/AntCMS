<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;
use AntCMS\AntConfig;
use AntCMS\AntCache;
use AntCMS\AntTools;
use AntCMS\AntTwig;
use Symfony\Contracts\Cache\ItemInterface;

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

            // Because we are only getting a list of files with the 'md' extension, we can blindly strip off the extension from each path.
            // Doing this creates more profesional looking URLs as AntCMS can automatically add the 'md' extenstion during the page rendering process.
            $pageFunctionalPath = substr(str_replace(antContentPath, "", $page), 0, -3);

            if ($pageFunctionalPath == '/index') {
                $pageFunctionalPath = '/';
            }

            if (str_ends_with($pageFunctionalPath, 'index')) {
                $pageFunctionalPath = substr($pageFunctionalPath, 0, -5);
            }

            $currentPage = array(
                'pageTitle' => $pageHeader['title'],
                'fullPagePath' => $page,
                'functionalPagePath' => ($pageFunctionalPath == DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : rtrim($pageFunctionalPath, DIRECTORY_SEPARATOR),
                'showInNav' => true,
            );

            // Move the index page to the first item in the page list, so it appears as the first item in the navbar. 
            if ($pageFunctionalPath == DIRECTORY_SEPARATOR) {
                array_unshift($pageList, $currentPage);
            } else {
                $pageList[] = $currentPage;
            }
        }

        AntYaml::saveFile(antPagesList, $pageList);
    }

    public static function getPages(): array
    {
        return AntYaml::parseFile(antPagesList);
    }

    /**
     * @param string $currentPage optional - What page is the active page. Used for highlighting the active page in the navbar
     */
    public static function generateNavigation(string $navTemplate = '', string $currentPage = ''): string
    {
        $pages = AntPages::getPages();
        $antCache = new AntCache;

        $theme = AntConfig::currentConfig('activeTheme');
        $cacheKey = $antCache->createCacheKey(json_encode($pages), $theme . $currentPage);

        return $antCache->get($cacheKey, function (ItemInterface $item) use ($navTemplate, $currentPage, $pages): string {
            $item->expiresAfter(AntCache::$defaultLifespan / 7);
            $baseURL = AntConfig::currentConfig('baseURL');
            foreach ($pages as $key => $page) {
                $url = "//" . AntTools::repairURL($baseURL . $page['functionalPagePath']);
                $pages[$key]['url'] = $url;
                $pages[$key]['active'] = $currentPage == $page['functionalPagePath'];

                //Remove pages that are hidden from the nav from the array before sending it to twig. 
                if (!(bool)$page['showInNav']) {
                    unset($pages[$key]);
                }
            }

            $antTwig = new AntTwig();
            $navHTML = $antTwig->renderWithTiwg($navTemplate, array('pages' => $pages));
            return $navHTML;
        });
    }
}
