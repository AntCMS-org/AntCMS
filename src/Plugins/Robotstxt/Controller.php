<?php

namespace AntCMS\Plugins\Robotstxt;

use Flight;
use AntCMS\Config;
use AntCMS\Tools;

class Controller
{
    public function __construct()
    {
        Flight::route("GET /robots.txt", function (): void {
            $protocol = Config::get('forceHTTPS') ? 'https' : Flight::request()->scheme;
            $baseURL = Config::get('baseURL');

            echo 'User-agent: *' . PHP_EOL;
            echo 'Sitemap: ' . $protocol . '://' . Tools::repairURL($baseURL . '/sitemap.xml' . PHP_EOL);
            Flight::response()->setHeader('Content-Type', 'text/plain');
        });
    }
}
