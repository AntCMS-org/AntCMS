<?php

namespace Plugins\Sitemap;

use AntCMS\AntPages;
use AntCMS\AntConfig;
use AntCMS\AntTools;
use Psr\Http\Message\ResponseInterface as Response;

class Sitemap extends \AntCMS\AntPlugin
{
    public function returnSitemap(): Response
    {
        $protocol = AntConfig::currentConfig('forceHTTPS') ? 'https' : 'http';
        $baseURL = AntConfig::currentConfig('baseURL');

        $pages = AntPages::getPages();

        if (extension_loaded('dom')) {
            $domDocument = new \DOMDocument('1.0', 'UTF-8');
            $domDocument->formatOutput = true;

            $domElement = $domDocument->createElement('urlset');
            $domElement->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $domDocument->appendChild($domElement);

            $urls = array();
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

            $response = $this->response->withHeader('Content-Type', 'Content-Type: application/xml');
            $response->getBody()->write($domDocument->saveXML());
            return $response;
        } else {
            $response = $this->response;
            $response->getBody()->write("AntCMS is unable to generate a sitemap without having the DOM extension loadded in PHP.");
            return $response;
        }
    }

    public function getName(): string
    {
        return 'Sitemap';
    }
}
