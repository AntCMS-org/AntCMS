<?php

use AntCMS\PluginController;
use HostByBelle\CompressionBuffer;
use AntCMS\AntCMS;
use AntCMS\Enviroment;
use AntCMS\Tools;
use AntCMS\Config;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('error_log', 'php_error.log');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';

$AntCMS = new AntCMS();

// Add a response body callback to display mem usage and time spent
Flight::response()->addResponseBodyCallback(function ($body) {
    if (Config::get('debug')) {
        return str_replace('<!--AntCMS-Debug-->', Tools::buildDebugInfo(), $body);
    }
    return $body;
});

// Setup CompressionBuffer & enable it in Flight
CompressionBuffer::setUp(true, false, [Flight::response(), 'header']);
if (doOutputCompression) {
    Flight::response()->addResponseBodyCallback([CompressionBuffer::class, 'handler']);
}

// HTTPS redirects
if (!Flight::request()->secure && !Enviroment::isCli() && Config::get('forceHttps')) {
    Flight::redirect('https://' . Flight::request()->host . Flight::request()->url);
    exit;
}

// Asset delivery
Flight::route('GET /themes/*/assets/*', [$AntCMS, 'serveContent']);

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
