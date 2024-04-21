<?php

// Registering constants
const AntDir = __DIR__;
const AntCachePath = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
const antConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.yaml';
const antPagesList = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Pages.yaml';
const antUsersList = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Users.yaml';
const antContentPath = __DIR__ . DIRECTORY_SEPARATOR . 'Content';
const antThemePath = __DIR__ . DIRECTORY_SEPARATOR . 'Themes';
const antPluginPath = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';

/**
 * If the server is modern enough to have xxh128, use that. It is really fast and still produces long hashes
 * If not, use MD4 since it's still quite fast.
 * Source: https://php.watch/articles/php-hash-benchmark
 */
if (in_array('xxh128', hash_algos())) {
    define('HASH_ALGO', 'xxh128');
} else {
    define('HASH_ALGO', 'md4');
}

// Load the Vendor autoloader
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Setup and register AntLoader
$classMapPath = AntCachePath . DIRECTORY_SEPARATOR . 'classMap.php';
$loader = new \AntCMS\AntLoader(['path' => $classMapPath]);
$loader->addNamespace('AntCMS\\', __DIR__ . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->addNamespace('AntCMS\\Plugins\\', __DIR__ . DIRECTORY_SEPARATOR . 'Plugins');

$loader->checkClassMap();
$loader->register();
