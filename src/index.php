<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

const appDir = __DIR__;
require_once __DIR__ . '/Vendor/autoload.php';
require_once __DIR__ . '/AntCMS/App.php';
require_once __DIR__ . '/AntCMS/Markdown.php';

use \AntCMS;
$antCms = new AntCMS\AntCMS();
$requestedPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$indexes = ['/', '/index.php', '/index.html'];
if (in_array($requestedPage, $indexes)) {
    $antCms->renderPage('index');
} else {
    $antCms->renderPage($requestedPage);
}
