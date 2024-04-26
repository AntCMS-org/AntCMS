<?php

namespace AntCMS;

class PluginController
{
    public static array $plugins = [];

    /**
     * Registers all plugin routes & sets up needed info for the plugin controller
     */
    public static function init(): void
    {
        $list = scandir(PATH_PLUGINS);
        if (count($list) >= 2 && $list[0] === '.' && $list[1] === '..') {
            unset($list[0]);
            unset($list[1]);
        }

        foreach($list as $pluginName) {
            $className = "\AntCMS\\Plugins\\$pluginName\\Controller";
            if(class_exists($className)) {
                new $className();
            }
        }
    }
}
