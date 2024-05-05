<?php

use AntCMS\{AntCMS, Config, Enviroment, HookController, PluginController, Tools};
use HostByBelle\CompressionBuffer;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('error_log', 'php_error.log');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

$AntCMS = new AntCMS();

// Add a response body callback to display debug info
Flight::response()->addResponseBodyCallback(fn ($body): string => str_replace('<!--AntCMS-Debug-->', Tools::buildDebugInfo(), $body));

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp(true, false, [Flight::response(), 'header']);
if (COMPRESS_OUTPUT) {
    Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);
}

Flight::response()->addResponseBodyCallback(function ($body) {
    HookController::fire('performanceMetricsBuilt', tools::getPerformanceMetrics());
    return $body;
});

// HTTPS redirects
if (!Flight::request()->secure && !Enviroment::isCli() && Config::get('forceHttps')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

// Asset delivery
Flight::route('GET /themes/*/assets/*', [$AntCMS, 'serveContent']);
Flight::route('GET /favicon.ico', function () use ($AntCMS): void {
    $AntCMS->serveContent(PATH_CURRENT_THEME . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'favicon.ico');
});

/// ACME challenges for certificate renewals
Flight::route('GET .well-known/acme-challenge/*', [$AntCMS, 'serveContent']);

// Register routes for plugins
PluginController::init();

Flight::route('GET /*', function () use ($AntCMS): void {
    if ((Flight::request()->url === '' || Flight::request()->url == '/') && !file_exists(PATH_USERS)) {
        // TODO for once plugin functionality is rebuilt
        //AntCMS::redirect('/profile/firsttime');
    }
    echo $AntCMS->renderPage();
});

Flight::start();
