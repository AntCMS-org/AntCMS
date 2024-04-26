<?php

use AntCMS\Config;

define('START', hrtime(true));

// Registering constants
const BASE_DIR = __DIR__;
const PATH_CACHE = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
const PATH_CONFIG = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.yaml';
const PATH_USERS = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Users.yaml';
const PATH_CONTENT = __DIR__ . DIRECTORY_SEPARATOR . 'Content';
const PATH_THEMES = __DIR__ . DIRECTORY_SEPARATOR . 'Themes';
const PATH_PLUGINS = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';

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
$classMapPath = PATH_CACHE . DIRECTORY_SEPARATOR . 'classMap.php';
$loader = new \AntCMS\AntLoader(['path' => $classMapPath]);
$loader->addNamespace('AntCMS\\', __DIR__ . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->addNamespace('AntCMS\\Plugins\\', __DIR__ . DIRECTORY_SEPARATOR . 'Plugins');

$loader->checkClassMap();
$loader->register();

// First-time related checks
if (!file_exists(PATH_CONFIG)) {
    Config::generateConfig();
}

// Define config-related constants
$config = Config::get();
define('COMPRESS_TEXT_ASSETS', $config['performance']['compressTextAssets']);
define('COMPRESS_OUTPUT', $config['performance']['doOutputCompression']);
define('COMPRESS_IMAGES', $config['performance']['compressImageAssets']);
define('BASE_URL', $config['baseUrl']);
define('DEBUG_LEVEL', $config['debugLevel']);
