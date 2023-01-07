<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

const AntDir = __DIR__;
const AntCachePath = __DIR__ . '/Cache';
const antConfigFile = __DIR__ . '/config.ymal';
const antPagesList = __DIR__ . '/pages.ymal';
const antContentPath = __DIR__ . '/Content';
require_once __DIR__ . '/Vendor/autoload.php';
require_once __DIR__ . '/Autoload.php';

use AntCMS\AntCMS;
use AntCMS\AntConfig;
use AntCMS\AntPages;

$antCms = new AntCMS();

if(!file_exists(antConfigFile)){
    AntConfig::generateConfig();
}

if(!file_exists(antPagesList)){
    AntPages::generatePages();
}

$currentConfg = AntConfig::currentConfig();

if ($currentConfg['forceHTTPS'] && 'cli' !== PHP_SAPI){
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

    if(!$isHTTPS){
        $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $url);
        exit;
    }
}

$requestedPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$indexes = ['/', '/index.php', '/index.html'];
if (in_array($requestedPage, $indexes)) {
    $antCms->renderPage('/');
} else {
    $antCms->renderPage($requestedPage);
}
