<?php

namespace AntCMS\Plugins\Sitemap;

use Flight;
use AntCMS\Config;
use AntCMS\Tools;
use AntCMS\Pages;

class Controller
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
                foreach ($pages as $key => $value) {
                    $urls[$key]['url'] = $value['functionalPagePath'];
                    $urls[$key]['lastchange'] = date('Y-m-d', filemtime($value['fullPagePath']));
                }

                foreach ($urls as $url) {
                    $element = $domDocument->createElement('url');

                    $loc = $domDocument->createElement('loc', $protocol . '://' . Tools::repairURL(baseUrl . $url['url']));
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
}
