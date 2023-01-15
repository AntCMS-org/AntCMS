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
            $doc = new DOMDocument();
            $doc->formatOutput = true;

            $root = $doc->createElement('urlset');
            $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $doc->appendChild($root);

            $urls = array();
            foreach ($pages as $key => $value) {
                $urls[$key]['url'] = $value['functionalPagePath'];
                $urls[$key]['lastchange'] = date('Y-m-d', filemtime($value['fullPagePath']));
            }

            foreach ($urls as $url) {
                $element = $doc->createElement('url');

                $loc = $doc->createElement('loc', $protocol . '://' . AntTools::repairURL($baseURL . $url['url']));
                $element->appendChild($loc);

                $lastmod = $doc->createElement('lastmod', $url['lastchange']);
                $element->appendChild($lastmod);

                $root->appendChild($element);
            }

            header('Content-Type: application/xml');
            echo $doc->saveXML();
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
