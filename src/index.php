<?php

use AntCMS\PluginController;
use HostByBelle\CompressionBuffer;
use AntCMS\AntCMS;
use AntCMS\Config;
use AntCMS\Enviroment;

error_reporting(E_ALL);
ini_set('display_errors', '1');
define('START', hrtime(true));

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

// Add a response body callback to display mem usage and time spent
Flight::response()->addResponseBodyCallback(function ($body) {
    if (Config::currentConfig('debug')) {
        $elapsed_time = round((hrtime(true) - START) / 1e+6, 2);
        $mem_usage = round(memory_get_peak_usage() / 1e+6, 2);
        return str_replace(
            '<!--AntCMS-Debug-->',
            "<p>Took $elapsed_time milliseconds to render the page with $mem_usage MB of RAM used.</p>",
            $body
        );
    } else {
        return $body;
    }
});

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp();
Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);

// HTTPS redirects
if (!Flight::request()->secure && !Enviroment::isCli() && Config::currentConfig('forceHTTPS')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

// Asset delivery
Flight::route('GET /themes/*/assets', function () use ($antCms, $requestUri): void {
    $antCms->serveContent(AntDir . $requestUri);
});

/// ACME challenges for certificate renewals
Flight::route('GET .well-known/acme-challenge/*', function () use ($antCms, $requestUri): void {
    $antCms->serveContent(AntDir . $requestUri);
});

// Register routes for plugins
PluginController::init();

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
