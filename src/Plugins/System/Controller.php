<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS\Plugins\System;

use AntCMS\AbstractPlugin;
use AntCMS\AntCMS;
use AntCMS\HookController;

class Controller extends AbstractPlugin
{
    /**
     * @var array<string, string>
     */
    private array $hooks = [
        'onAfterContentHit' => 'This is fired when markdown content is accessed. The URI will be passed in the data.',
        'onAfterPerformanceMetricsBuilt' => 'When fired, this event contains all performance metrics AntCMS was able to collect on a request. These are more complete & accurate than the metrics shown on the bottom of the screen.',
        'onBeforeApiCalled' => 'This event is fired before an API endpoint is called.',
        'onAfterApiCalled' => 'This event is fired after an API endpoint is called and the response is available.',
        'onHookFireComplete' => 'This event is fired when others have completed. The data provided will include the hook name, timing data, and parameter read / update statistics.',
        'onBeforeMarkdownParsed' => 'This event is fired before markdown is converted, allowing for pre-processing before the markdown is run through the parser.',
        'onAfterMarkdownParsed' => 'This is fired after markdown is converted, allowing you to modify generated markdown content.',
        'onAfterPluginsInit' => 'This event is fired after all plugins have been initialized.',
        'onBeforeOutputFlushed' => 'This event is fired right before the generated response is finalized (compressed) and sent to the browser. No later chances to modify the output buffer exist.',
        'onBeforeCronRun' => 'This cron event is fired before the cron tasks are performed.',
        'onAfterCronRun' => 'This cron event is fired after the cron tasks are completed.',
    ];

    public function __construct()
    {
        // Register AntCMS's built in events
        foreach ($this->hooks as $name => $description) {
            HookController::registerHook($name, $description);
        }

        HookController::registerCallback('onBeforeOutputFlushed', $this->appendDebugInfo(...));

        $this->addDisallow('/api/*');
    }

    private function appendDebugInfo(\AntCMS\Event $event): \AntCMS\Event
    {
        $params = $event->getParameters();
        $params['output'] = str_replace('<!--AntCMS-Debug-->', \AntCMS\Tools::buildDebugInfo(), $params['output'] ?? '');
        $event->setParameters($params);
        return $event;
    }
}
