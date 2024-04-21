<?php

namespace AntCMS;

use AntCMS\AntCMS;
use AntCMS\AntYaml;
use AntCMS\Config;
use AntCMS\Cache;
use AntCMS\Tools;
use AntCMS\Twig;
use Symfony\Contracts\Cache\ItemInterface;

class Pages
{
    public static function generatePages(): void
    {
        $pages = Tools::getFileList(antContentPath, 'md', true);
        $pageList = [];

        foreach ($pages as $page) {
            $page = Tools::repairFilePath($page);
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

            $currentPage = ['pageTitle' => $pageHeader['title'], 'fullPagePath' => $page, 'functionalPagePath' => ($pageFunctionalPath == DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : rtrim($pageFunctionalPath, DIRECTORY_SEPARATOR), 'showInNav' => true];

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

    public static function getNavList(string $currentPage = ''): array
    {
        $pages = self::getPages();

        $baseURL = Config::currentConfig('baseURL');
        foreach ($pages as $key => $page) {
            $url = "//" . Tools::repairURL($baseURL . $page['functionalPagePath']);
            $pages[$key]['url'] = $url;
            $pages[$key]['active'] = $currentPage == $page['functionalPagePath'];

            //Remove pages that are hidden from the nav from the array before sending it to twig.
            if (!(bool) $page['showInNav']) {
                unset($pages[$key]);
            }
        }

        return $pages;
    }
}
