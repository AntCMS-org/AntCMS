<?php

namespace AntCMS\Plugins\System;

use AntCMS\{AbstractPlugin, HookController};

class Controller extends AbstractPlugin
{
    private array $hooks = [
        'contentHit' => 'This is fired when markdown content is accessed. The URI will be passed in the data.',
        'performanceMetricsBuilt' => 'When fired, this event contains all performance metrics AntCMS was able to collect on a request. These are more complete & accurate than the metrics shown on the bottom of the screen.',
    ];

    public function __construct()
    {
        // Register AntCMS's built in events
        foreach ($this->hooks as $name => $description) {
            HookController::registerHook($name, $description);
        }
    }
}
