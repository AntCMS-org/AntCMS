<?php

namespace AntCMS\Plugins\Robotstxt;

use Flight;
use AntCMS\Config;
use AntCMS\Tools;
use AntCMS\AbstractPlugin;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        Flight::route("GET /robots.txt", function (): void {
            $protocol = Config::get('forceHttps') ? 'https' : Flight::request()->scheme;
            echo 'User-agent: *' . PHP_EOL;
            echo 'Sitemap: ' . $protocol . '://' . Tools::repairURL(BASE_URL . '/sitemap.xml' . PHP_EOL);
            Flight::response()->setHeader('Content-Type', 'text/plain');
        });
    }
}
