<?php

namespace AntCMS;

abstract class Plugin
{
    /**
     * @param array<string> $route
     */
    public function handlePluginRoute(array $route): void
    {
        die("Plugin did not define a handlePluginRoute function");
    }

    /** @return string  */
    abstract public function getName();
}
