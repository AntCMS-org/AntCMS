<?php

namespace AntCMS;

abstract class AntPlugin
{
    public function displayRoute($route)
    {
        die("Plugin did not define a displayRoute function");
    }

    abstract function getName();
}
