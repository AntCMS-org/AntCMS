<?php

namespace AntCMS\Plugins\Sitemap;

use Flight;
use AntCMS\Config;
use AntCMS\Tools;
use AntCMS\Pages;
use AntCMS\AbstractPlugin;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        Flight::route("GET /sitemap.xml", function (): void {
            $protocol = Config::get('forceHttps') ? 'https' : Flight::request()->scheme;
            $pages = Pages::getPages();

            if (extension_loaded('dom')) {
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                $domDocument->formatOutput = true;

                $domElement = $domDocument->createElement('urlset');
                $domElement->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
                $domDocument->appendChild($domElement);

                $urls = [];
                $this->addPages($pages, $urls);

                foreach ($urls as $url) {
                    $element = $domDocument->createElement('url');

                    $loc = $domDocument->createElement('loc', $protocol . '://' . Tools::repairURL(BASE_URL . $url['url']));
                    $element->appendChild($loc);

                    $lastmod = $domDocument->createElement('lastmod', $url['lastchange']);
                    $element->appendChild($lastmod);

                    $domElement->appendChild($element);
                }

                echo $domDocument->saveXML();
                Flight::response()->header('Content-Type', 'application/xml');
            } else {
                Flight::halt(503, "AntCMS is unable to generate a sitemap without having the DOM extension loadded in PHP.");
            }
        });
    }

    private function addPages(array $list, array &$urls): void
    {
        foreach ($list as $item) {
            if (!array_key_exists('functionalPath', $item)) {
                $this->addPages($item, $urls);
                continue;
            }

            $urls[] = [
                'url' => $item['functionalPath'],
                'lastchange' => date('Y-m-d', filemtime($item['realPath'])),
            ];
        }
    }
}
