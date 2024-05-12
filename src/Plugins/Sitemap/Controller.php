<?php

namespace AntCMS\Plugins\Sitemap;

use AntCMS\PluginController;
use AntCMS\{AbstractPlugin, Config, Pages, Tools};
use Flight;

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

                $urls = PluginController::getSitemapUrls();
                $this->addPages($pages, $urls);

                foreach ($urls as $url) {
                    $entry = $domDocument->createElement('url');

                    $loc = $domDocument->createElement('loc', $protocol . '://' . Tools::repairURL(BASE_URL . $url['url']));
                    $entry->appendChild($loc);

                    if ($url['lastmod']) {
                        $lastmod = $domDocument->createElement('lastmod', date('Y-m-d', $url['lastmod']));
                        $entry->appendChild($lastmod);
                    }

                    $domElement->appendChild($entry);
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
                'lastmod' => filemtime($item['realPath']),
            ];
        }
    }
}
