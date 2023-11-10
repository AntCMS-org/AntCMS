<?php

use AntCMS\AntCMS;
use AntCMS\AntConfig;
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

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUrl = AntConfig::currentConfig('baseURL');
$antRouting = new \AntCMS\AntRouting($baseUrl, $requestUri);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$antCMS = new AntCMS($app);

if (AntConfig::currentConfig('forceHTTPS') && !\AntCMS\AntEnviroment::isCli()) {
    $app->addMiddleware(new Middlewares\Https());
}

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

/**
 * TODO: Make these non-static and not hard-coded.
 * This is also still relying on the custom routing implementation I am working to remove
 */
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

$app->get('/', function (Request $request, Response $response) use ($antCMS) {
    if (!file_exists(antUsersList)) {
        AntCMS::redirectWithoutRequest('/profile/firsttime');
    }

    $antCMS->setRequest($request);
    $antCMS->SetResponse($response);
    return $antCMS->renderPage();
});

$app->get('/{path:.*}', function (Request $request, Response $response) use ($antCMS) {
    $antCMS->setRequest($request);
    $antCMS->SetResponse($response);
    return $antCMS->renderPage();
});

$app->run();
