<?php

use AntCMS\AntCMS;
use AntCMS\AntConfig;
use AntCMS\AntPluginLoader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set("error_log", "php_error.log");

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

if (!file_exists(antConfigFile)) {
    AntConfig::generateConfig();
}

if (!file_exists(antPagesList)) {
    \AntCMS\AntPages::generatePages();
}

$antCMS = new AntCMS();

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$trailingSlash = new Middlewares\TrailingSlash();
$app->addMiddleware($trailingSlash->redirect());

if (AntConfig::currentConfig('forceHTTPS') && !\AntCMS\AntEnviroment::isCli()) {
    $app->addMiddleware(new Middlewares\Https());
}

if (AntConfig::currentConfig('cacheMode') !== 'none') {
    $routeCollector = $app->getRouteCollector();
    $routeCollector->setCacheFile(AntCachePath . DIRECTORY_SEPARATOR . 'routes.cache');
}

// Register plugin routes first so they get priority
$pluginLoader = new AntPluginLoader;
$pluginLoader->registerPluginRoutes($app);

$app->get('/themes/{theme}/assets', function (Request $request, Response $response) use ($antCMS) {
    $antCMS->setRequest($request);
    $antCMS->SetResponse($response);
    return $antCMS->serveContent();
});

$app->get('/.well-known/acme-challenge/{path:.*}', function (Request $request, Response $response) use ($antCMS) {
    $antCMS->setRequest($request);
    $antCMS->SetResponse($response);
    return $antCMS->serveContent();
});

$app->get('/{path:.*}', function (Request $request, Response $response) use ($antCMS) {
    if (!file_exists(antUsersList)) {
        AntCMS::redirectWithoutRequest('/profile/firsttime');
    }

    $antCMS->setRequest($request);
    $antCMS->SetResponse($response);
    return $antCMS->renderPage();
});

$app->run();
