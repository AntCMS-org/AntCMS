<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

const AntDir = __DIR__;
const AntCache = __DIR__ . '/Cache';
const antConfig = __DIR__ . '/config.ymal';
require_once __DIR__ . '/Vendor/autoload.php';
require_once __DIR__ . '/Autoload.php';

use AntCMS\AntCMS;

$antCms = new AntCMS();
$requestedPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$indexes = ['/', '/index.php', '/index.html'];
if (in_array($requestedPage, $indexes)) {
    $antCms->renderPage('index');
} else {
    $antCms->renderPage($requestedPage);
}
