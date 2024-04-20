<?php

use HostByBelle\CompressionBuffer;

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';

use AntCMS\AntCMS;
use AntCMS\AntConfig;

if (!file_exists(antConfigFile)) {
    AntConfig::generateConfig();
}

if (!file_exists(antPagesList)) {
    \AntCMS\AntPages::generatePages();
}

$antCms = new AntCMS();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrl = AntConfig::currentConfig('baseURL');
$antRouting = new \AntCMS\AntRouting($baseUrl, $requestUri);

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp();
Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);

if (AntConfig::currentConfig('forceHTTPS') && !\AntCMS\AntEnviroment::isCli()) {
    $antRouting->redirectHttps();
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
