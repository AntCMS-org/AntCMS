<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Constants.php';

$classMapPath = __DIR__  . DIRECTORY_SEPARATOR .  'Cache'  . DIRECTORY_SEPARATOR .  'classMap.php';
$loader = new AntCMS\AntLoader($classMapPath);
$loader->addPrefix('AntCMS\\', __DIR__  . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->checkClassMap();
$loader->register();

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

if (AntConfig::currentConfig('forceHTTPS') && !\AntCMS\AntEnviroment::isCli()) {
    $antRouting->redirectHttps();
}

if ($antRouting->checkMatch('/themes/*/assets')) {
    $antCms->serveContent(AntDir . $requestUri);
}

if ($antRouting->checkMatch('/.well-known/acme-challenge/*')) {
    $antCms->serveContent(AntDir . $requestUri);
}

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

if ($antRouting->isIndex()) {
    // If the users list hasn't been created, redirect to the first-time setup
    if (!file_exists(antUsersList)) {
        AntCMS::redirect('/profile/firsttime');
    }

    echo $antCms->renderPage('/');
    exit;
} else {
    echo $antCms->renderPage($requestUri);
    exit;
}
