<?php

use AntCMS\Plugin;
use AntCMS\Config;
use AntCMS\Tools;

class RobotstxtPlugin extends Plugin
{
    public function handlePluginRoute(array $route): void
    {
        $protocol = Config::currentConfig('forceHTTPS') ? 'https' : 'http';
        $baseURL = Config::currentConfig('baseURL');

        $robotstxt = 'User-agent: *' . "\n";
        $robotstxt .= 'Disallow: /plugin/' . "\n";
        $robotstxt .= 'Disallow: /admin/' . "\n";
        $robotstxt .= 'Disallow: /profile/' . "\n";
        $robotstxt .= 'Sitemap: ' . $protocol . '://' . Tools::repairURL($baseURL . '/sitemap.xml' . "\n");
        header("Content-Type: text/plain");
        echo $robotstxt;
        exit;
    }

    public function getName(): string
    {
        return 'Robotstxt';
    }
}
