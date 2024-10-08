<?php

namespace AntCMS\Plugins\System;

use AntCMS\AbstractPlugin;
use AntCMS\HookController;

class Controller extends AbstractPlugin
{
    /**
     * @var array<string, string>
     */
    private array $hooks = [
        'contentHit' => 'This is fired when markdown content is accessed. The URI will be passed in the data.',
        'performanceMetricsBuilt' => 'When fired, this event contains all performance metrics AntCMS was able to collect on a request. These are more complete & accurate than the metrics shown on the bottom of the screen.',
        'beforeApiCalled' => 'This event is fired before an API endpoint is called',
        'afterApiCalled' => 'This event is fired after an API endpoint is called and the response is available',
        'onHookFireComplete' => 'This event is fired when others have completed. The data provided will include the hook name, timing data, and parameter read / update statistics.',
        'onBeforeMarkdownParsed' => 'This event is fired before markdown is converted, allowing for pre-processing before the markdown is run through the parser',
        'onAfterMarkdownParsed' => 'This is fired after markdown is converted, allowing you to modify generated markdown content',
    ];

    public function __construct()
    {
        // Register AntCMS's built in events
        foreach ($this->hooks as $name => $description) {
            HookController::registerHook($name, $description);
        }

        $this->addDisallow('/api/*');
    }
}
