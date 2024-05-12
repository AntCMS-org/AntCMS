<?php

namespace AntCMS\Plugins\Robotstxt;

use AntCMS\AbstractPlugin;
use AntCMS\Config;
use AntCMS\PluginController;
use AntCMS\Tools;

use Flight;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        Flight::route("GET /robots.txt", function (): void {
            $protocol = Config::get('forceHttps') ? 'https' : Flight::request()->scheme;
            echo 'User-agent: *' . PHP_EOL;

            $additions = PluginController::getRobotsTxtEntries();
            foreach ($additions['allow'] as $url) {
                echo 'Allow: ' . $url . PHP_EOL;
            }

            foreach ($additions['disallow'] as $url) {
                echo 'Disallow: ' . $url . PHP_EOL;
            }

            echo 'Sitemap: ' . $protocol . '://' . Tools::repairURL(BASE_URL . '/sitemap.xml' . PHP_EOL);
            Flight::response()->setHeader('Content-Type', 'text/plain');
        });
    }
}
