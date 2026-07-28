<?php

/**
 * Copyright 2026 AntCMS
 */

use AntCMS\Cache;
use AntCMS\HookController;
use AntCMS\PluginController;

class runCron extends Ahc\Cli\Input\Command
{
    public function __construct()
    {
        parent::__construct('runCron', 'Runs the system cronjob.');
    }

    public function execute(): void
    {
        $color = new Ahc\Cli\Output\Color();
        echo $color->comment("Initializing plugins\n");
        PluginController::init();

        echo $color->comment("Firing the onBeforeCronRun event\n");
        HookController::fire("onBeforeCronRun");

        echo $color->comment("Pruning system cache\n");
        Cache::prune();

        echo $color->comment("Firing the onAfterCronRun event\n");
        HookController::fire("onAfterCronRun");

        echo $color->ok("Done!\n");
    }
}
