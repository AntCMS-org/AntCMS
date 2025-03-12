<?php

use AntCMS\AntCMS;
use AntCMS\ApiController;
use AntCMS\Config;
use AntCMS\Enviroment;
use AntCMS\HookController;
use AntCMS\PluginController;
use AntCMS\Tools;
use HostByBelle\CompressionBuffer;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('error_log', 'php_error.log');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

$AntCMS = new AntCMS();

// Use hooks to perform any final changes to the output buffer before compressing and sending it
Flight::response()->addResponseBodyCallback(function (string $body): string {
    $event = HookController::fire('onBeforeOutputFlushed', ['output' => $body]);
    return $event->getParameters()['output'];
});

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp(true, false, [Flight::response(), 'header']);
if (COMPRESS_OUTPUT) {
    Flight::response()->addResponseBodyCallback(CompressionBuffer::handler(...));
}

Flight::response()->addResponseBodyCallback(function ($body) {
    HookController::fire('onAfterPerformanceMetricsBuilt', tools::getPerformanceMetrics());
    return $body;
});

// Asset delivery
Flight::route('GET /themes/*/assets/*', $AntCMS->serveContent(...));
Flight::route('GET /favicon.ico', function () use ($AntCMS): void {
    $AntCMS->serveContent(PATH_CURRENT_THEME . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'favicon.ico');
});

/// ACME challenges for certificate renewals
Flight::route('GET .well-known/acme-challenge/*', $AntCMS->serveContent(...));

// API Controller
Flight::group('/api/v0', function (): void {
    $controller = new ApiController();
    Flight::route('/public/@plugin/@method/*', $controller->publicController(...));
    Flight::route('/protected/@plugin/@method/*', $controller->privateController(...));
});

// Register routes for plugins
PluginController::init();

// HTTPS redirects
if (!Flight::request()->secure && !Enviroment::isCli() && Config::get('forceHttps')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

Flight::route('GET /*', function () use ($AntCMS): void {
    if ((Flight::request()->url === '' || Flight::request()->url == '/') && !file_exists(PATH_USERS)) {
        // TODO for once plugin functionality is rebuilt
        //AntCMS::redirect('/profile/firsttime');
    }
    echo $AntCMS->renderPage();
});

Flight::start();
