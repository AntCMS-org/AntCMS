<?php

/**
 * Copyright 2026 AntCMS
 */

use AntCMS\HookController;
use AntCMS\PluginController;

class listHooks extends Ahc\Cli\Input\Command
{
    public function __construct()
    {
        parent::__construct('listHooks', 'Lists all hooks that are registered via plugin initialization.');
    }

    public function execute(): void
    {
        PluginController::init();
        $color = new Ahc\Cli\Output\Color();

        // For each hook display it's stats
        foreach (HookController::getHookList() as $hook) {
            echo $color->info($hook->name) . "\n";
            echo " - " . $color->comment($hook->description) . "\n";
            $registeredCallbacks = $hook->registeredCallbacks;
            if ($registeredCallbacks < 10) {
                echo " - Listeners: " . $color->ok("{$registeredCallbacks}") . "\n" . "\n";
            } else {
                echo " - Listeners: " . $color->warn("{$registeredCallbacks}") . "\n" . "\n";
            }
        }
    }
}
