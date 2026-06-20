<?php

declare(strict_types=1);

/**
 * Copyright 2026 AntCMS
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Register plugins so hook tests can function correctly
AntCMS\PluginController::init();
