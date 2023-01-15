<?php

use AntCMS\AntPlugin;
use AntCMS\AntConfig;
use AntCMS\AntTools;

class RobotstxtPlugin extends AntPlugin
{
    public function handlePluginRoute(array $route)
    {
        $protocol = AntConfig::currentConfig('forceHTTPS') ? 'https' : 'http';
        $baseURL = AntConfig::currentConfig('baseURL');

        $robotstxt = 'User-agent: *' . "\n";
        $robotstxt.= 'Disallow: /plugin/' . "\n";
        $robotstxt.= 'Sitemap: ' . $protocol . '://' . AntTools::repairURL($baseURL . '/sitemap.xml' . "\n");
        header("Content-Type: text/plain");
        echo $robotstxt;
        exit;
    }
    public function getName(): string
    {
        return 'Robotstxt';
    }
}
