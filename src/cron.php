<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$classMapPath = __DIR__  . DIRECTORY_SEPARATOR .  'Cache'  . DIRECTORY_SEPARATOR .  'classMap.php';
$loader = new AntCMS\AntLoader($classMapPath);
$loader->addPrefix('AntCMS\\', __DIR__  . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->checkClassMap();
$loader->register();

AntCMS\AntCache::clearCache();
