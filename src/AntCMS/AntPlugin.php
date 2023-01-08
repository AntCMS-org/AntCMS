<?php

namespace AntCMS;

abstract class AntPlugin
{
    public function handlePluginRoute(array $route)
    {
        die("Plugin did not define a handlePluginRoute function");
    }

    abstract function getName();
}
