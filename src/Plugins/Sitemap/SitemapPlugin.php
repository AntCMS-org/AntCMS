<?php

use AntCMS\AntPlugin;
use AntCMS\AntPages;
use AntCMS\AntConfig;
use AntCMS\AntTools;

class SitemapPlugin extends AntPlugin
{
    public function handlePluginRoute(array $route)
    {
        $protocol = AntConfig::currentConfig('forceHTTPS') ? 'https' : 'http';
        $baseURL = AntConfig::currentConfig('baseURL');

        $pages = AntPages::getPages();

        if (extension_loaded('dom')) {
            $domDocument = new DOMDocument('1.0', 'UTF-8');
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

                $loc = $domDocument->createElement('loc', $protocol . '://' . AntTools::repairURL($baseURL . $url['url']));
                $element->appendChild($loc);

                $lastmod = $domDocument->createElement('lastmod', $url['lastchange']);
                $element->appendChild($lastmod);

                $domElement->appendChild($element);
            }

            header('Content-Type: application/xml');
            echo $domDocument->saveXML();
            exit;
        } else {
            die("AntCMS is unable to generate a sitemap without having the DOM extension loadded in PHP.");
        }
    }

    public function getName(): string
    {
        return 'Sitemap';
    }
}
