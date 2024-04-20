<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$classMapPath = __DIR__  . DIRECTORY_SEPARATOR .  'Cache'  . DIRECTORY_SEPARATOR .  'classMap.php';
$loader = new \AntCMS\AntLoader(['path' => $classMapPath]);
$loader->addNamespace('AntCMS\\', __DIR__  . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->checkClassMap();
$loader->register();

$antCache = new \AntCMS\Cache();
$antCache->purge();
