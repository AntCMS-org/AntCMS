<?php

namespace Plugins\Robotstxt;

use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntTools;

class Robotstxt extends AntPlugin
{
    public function returnRobotstxt()
    {
        $protocol = AntConfig::currentConfig('forceHTTPS') ? 'https' : 'http';
        $baseURL = AntConfig::currentConfig('baseURL');

        $robotstxt = 'User-agent: *' . "\n";
        $robotstxt .= 'Disallow: /plugin/' . "\n";
        $robotstxt .= 'Disallow: /admin/' . "\n";
        $robotstxt .= 'Disallow: /profile/' . "\n";
        $robotstxt .= 'Sitemap: ' . $protocol . '://' . AntTools::repairURL($baseURL . '/sitemap.xml' . "\n");

        $response = $this->response->withHeader('Content-Type', 'Content-Type: text/plain');
        $response->getBody()->write($robotstxt);
        return $response;
    }

    public function getName(): string
    {
        return 'Robotstxt';
    }
}
