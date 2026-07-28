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
            if ($hook->registeredCallbacks < 10) {
                echo " - Listeners: " . $color->ok($hook->registeredCallbacks) . "\n" . "\n";
            } else {
                echo " - Listeners: " . $color->warn($hook->registeredCallbacks) . "\n" . "\n";
            }
        }
    }
}
