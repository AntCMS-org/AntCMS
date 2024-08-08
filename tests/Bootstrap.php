<?php

// Load the standard AntCMS Bootstrap file
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Register plugins so hook tests can function correctly
AntCMS\PluginController::init();
