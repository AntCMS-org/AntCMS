<?php

/**
 * Copyright 2026 AntCMS
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'Vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require_once __DIR__ . '/bootstrap.php';

use AntCMS\Environment;
use Symfony\Component\Finder\Finder;

if (!Environment::isCli()) {
    echo "CLI only";
    exit(500);
}

// Init App with name and version
$app = new Ahc\Cli\Application('AntCMS CLI', 'v0.0.1');

// Using finder, find and register all installed commands
$finder = new Finder();
$finder->name("*.php")->in(PATH_PLUGINS . "/*/Commands");
if ($finder->hasResults()) {
    foreach ($finder as $file) {
        require_once $file->getRealPath();
        $className = $file->getFilenameWithoutExtension();
        if (class_exists($className)) {
            $app->add(new $className());
        }
    }
}

$app->logo('--- AntCMS ---');
$app->handle($_SERVER['argv']);
