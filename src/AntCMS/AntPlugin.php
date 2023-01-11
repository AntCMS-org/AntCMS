<?php

namespace AntCMS;

abstract class AntPlugin
{
    /**
     * @param array<string> $route 
     * @return mixed 
     */
    public function handlePluginRoute(array $route)
    {
        die("Plugin did not define a handlePluginRoute function");
    }

    /** @return string  */
    abstract function getName();
}
