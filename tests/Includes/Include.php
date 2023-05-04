<?php
$basedir = dirname(__DIR__, 2);
$srcdir = $basedir . DIRECTORY_SEPARATOR . 'src';

include_once $srcdir . DIRECTORY_SEPARATOR . 'Constants.php';

$classMapPath = $srcdir  . DIRECTORY_SEPARATOR .  'Cache'  . DIRECTORY_SEPARATOR .  'classMap.php';
$loader = new AntCMS\AntLoader($classMapPath );
$loader->addPrefix('AntCMS\\', $srcdir  . DIRECTORY_SEPARATOR . 'AntCMS');
$loader->checkClassMap();
$loader->register();
