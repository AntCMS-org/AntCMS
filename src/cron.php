<?php

declare(strict_types=1);

/**
 * Copyright 2025 AntCMS
 */

use AntCMS\Cache;
use AntCMS\HookController;
use AntCMS\PluginController;

require __DIR__ . '/bootstrap.php';

PluginController::init();

HookController::fire("onBeforeCronRun");
Cache::prune();
HookController::fire("onAfterCronRun");
