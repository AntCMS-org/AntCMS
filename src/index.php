<?php

use AntCMS\PluginController;
use HostByBelle\CompressionBuffer;
use AntCMS\AntCMS;
use AntCMS\Config;
use AntCMS\Enviroment;
use AntCMS\Tools;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('error_log', 'php_error.log');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';

if (!file_exists(antConfigFile)) {
    Config::generateConfig();
}

if (!file_exists(antPagesList)) {
    \AntCMS\Pages::generatePages();
}

$AntCMS = new AntCMS();

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Add a response body callback to display mem usage and time spent
Flight::response()->addResponseBodyCallback(function ($body) {
    if (Config::currentConfig('debug')) {
        return str_replace('<!--AntCMS-Debug-->', Tools::buildDebugInfo(), $body);
    } else {
        return $body;
    }
});

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp(true, false, [Flight::response(), 'header']);
if (Config::currentConfig('performance.doOutputCompression')) {
    Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);
}

// HTTPS redirects
if (!Flight::request()->secure && !Enviroment::isCli() && Config::currentConfig('forceHTTPS')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

// Asset delivery
Flight::route('GET /themes/*/assets/*', function () use ($AntCMS, $requestUri): void {
    $AntCMS->serveContent(AntDir . $requestUri);
});

/// ACME challenges for certificate renewals
Flight::route('GET .well-known/acme-challenge/*', function () use ($AntCMS, $requestUri): void {
    $AntCMS->serveContent(AntDir . $requestUri);
});

// Register routes for plugins
PluginController::init();

Flight::route('GET /', function () use ($AntCMS): void {
    if (!file_exists(antUsersList)) {
        // TODO for once plugin functionality is rebuilt
        //AntCMS::redirect('/profile/firsttime');
    }
    echo $AntCMS->renderPage('/');
});

Flight::route('GET /*', function () use ($AntCMS, $requestUri): void {
    echo $AntCMS->renderPage($requestUri);
});

Flight::start();
