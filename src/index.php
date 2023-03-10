<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Constants.php';

use AntCMS\AntCMS;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntPluginLoader;

if (!file_exists(antConfigFile)) {
    AntConfig::generateConfig();
}

if (!file_exists(antPagesList)) {
    AntPages::generatePages();
}

$antCms = new AntCMS();

if (AntConfig::currentConfig('forceHTTPS') && 'cli' !== PHP_SAPI) {
    $isHTTPS = false;

    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        $isHTTPS = true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
        $isHTTPS = true;
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) !== 'off') {
        $isHTTPS = true;
    }

    if (!$isHTTPS) {
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $url);
        exit;
    }
}

$requestedPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = explode('/', $requestedPage);

if ($segments[0] === '') {
    array_shift($segments);
}

if ($segments[0] === 'themes' && $segments[2] === 'assets') {
    $antCms->serveContent(AntDir . $requestedPage);
    exit;
}

if ($segments[0] == 'sitemap.xml') {
    $segments[0] = 'plugin';
    $segments[1] = 'sitemap';
}

if ($segments[0] == 'robots.txt') {
    $segments[0] = 'plugin';
    $segments[1] = 'robotstxt';
}

if ($segments[0] == 'admin') {
    array_unshift($segments, 'plugin');
}

if ($segments[0] == 'profile') {
    array_unshift($segments, 'plugin');
}

if ($segments[0] === 'plugin') {
    $pluginName = $segments[1];
    $pluginLoader = new AntPluginLoader();
    $plugins = $pluginLoader->loadPlugins();

    //Drop the first two elements of the array so the remaining segments are specific to the plugin.
    array_splice($segments, 0, 2);

    foreach ($plugins as $plugin) {
        if (strtolower($plugin->getName()) === strtolower($pluginName)) {
            $plugin->handlePluginRoute($segments);
            exit;
        }
    }

    // plugin not found
    header("HTTP/1.0 404 Not Found");
    echo ("Error 404");
    exit;
}

$indexes = ['/', '/index.php', '/index.html'];
if (in_array($segments[0], $indexes) or empty($segments[0])) {

    // If the users list hasn't been created, redirect to the first-time setup
    if (!file_exists(antUsersList)) {
        AntCMS::redirect('/profile/firsttime');
    }

    echo $antCms->renderPage('/');
    exit;
} else {
    echo $antCms->renderPage($requestedPage);
    exit;
}
