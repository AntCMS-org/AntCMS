<?php

const AntDir = __DIR__;
const AntCachePath = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
const antConfigFile = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Config.yaml';
const antPagesList = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Pages.yaml';
const antUsersList = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'Users.yaml';
const antContentPath = __DIR__ . DIRECTORY_SEPARATOR . 'Content';
const antThemePath = __DIR__ . DIRECTORY_SEPARATOR . 'Themes';
const antPluginPath = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';

if (in_array('xxh128', hash_algos())) {
    define('HAS_XXH128', true);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$classMapPath = AntCachePath . DIRECTORY_SEPARATOR . 'classMap.php';
$loader = new \AntCMS\AntLoader(['path' => $classMapPath]);
$loader->addNamespace('AntCMS\\', __DIR__ . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->addNamespace('AntCMS\\Plugins\\', __DIR__ . DIRECTORY_SEPARATOR . 'Plugins');

$loader->checkClassMap();
$loader->register();
