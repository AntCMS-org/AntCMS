<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

const AntDir = __DIR__;
const AntCachePath   = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
const antConfigFile  = __DIR__ . DIRECTORY_SEPARATOR . 'config.yaml';
const antPagesList   = __DIR__ . DIRECTORY_SEPARATOR . 'pages.yaml';
const antContentPath = __DIR__ . DIRECTORY_SEPARATOR . 'Content';
const antThemePath   = __DIR__ . DIRECTORY_SEPARATOR . 'Themes';
const antPluginPath  = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoload.php';

use AntCMS\AntCMS;
use AntCMS\AntConfig;
use AntCMS\AntPages;
use AntCMS\AntPluginLoader;

$antCms = new AntCMS();

if (!file_exists(antConfigFile)) {
    AntConfig::generateConfig();
}

if (!file_exists(antPagesList)) {
    AntPages::generatePages();
}

$currentConfg = AntConfig::currentConfig();

if ($currentConfg['forceHTTPS'] && 'cli' !== PHP_SAPI) {
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

$requestedPage = strtolower(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$segments = explode('/', $requestedPage);

if ($segments[0] === '') {
    array_shift($segments);
}

if ($segments[0] === 'themes' && $segments[2] === 'assets') {
    $antCms->serveContent(AntDir . $requestedPage);
    exit;
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
if (in_array($segments[0], $indexes)) {
    $antCms->renderPage('/');
    exit;
} else {
    $antCms->renderPage($requestedPage);
    exit;
}
