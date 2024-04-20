<?php

use HostByBelle\CompressionBuffer;
use AntCMS\AntCMS;
use AntCMS\Config;
use AntCMS\Enviroment;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';

if (!file_exists(antConfigFile)) {
    Config::generateConfig();
}

if (!file_exists(antPagesList)) {
    \AntCMS\Pages::generatePages();
}

$antCms = new AntCMS();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrl = Config::currentConfig('baseURL');

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp();
Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);

if (!Flight::request()->secure && !Enviroment::isCli() && Config::currentConfig('forceHTTPS')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

Flight::route('GET /themes/*/assets', function () use ($antCms, $requestUri): void {
    $antCms->serveContent(AntDir . $requestUri);
});

Flight::route('GET .well-known/acme-challenge/*', function () use ($antCms, $requestUri): void {
    $antCms->serveContent(AntDir . $requestUri);
});

/*
if ($antRouting->checkMatch('/sitemap.xml')) {
    $antRouting->setRequestUri('/plugin/sitemap');
}

if ($antRouting->checkMatch('/robots.txt')) {
    $antRouting->setRequestUri('/plugin/robotstxt');
}

if ($antRouting->checkMatch('/admin/*')) {
    $antRouting->requestUriUnshift('plugin');
}

if ($antRouting->checkMatch('/profile/*')) {
    $antRouting->requestUriUnshift('plugin');
}

if ($antRouting->checkMatch('/plugin/*')) {
    $antRouting->routeToPlugin();
}
*/

Flight::route('GET /', function () use ($antCms): void {
    if (!file_exists(antUsersList)) {
        // TODO for once plugin functionality is rebuilt
        //AntCMS::redirect('/profile/firsttime');
    }
    echo $antCms->renderPage('/');
});

Flight::route('GET /*', function () use ($antCms, $requestUri): void {
    echo $antCms->renderPage($requestUri);
});

Flight::start();
