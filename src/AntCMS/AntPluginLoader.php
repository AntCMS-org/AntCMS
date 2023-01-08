<?php

namespace AntCMS;

use AntCMS\AntTools;

class AntPluginLoader
{
    public function loadPlugins()
    {
        $plugins = array();
        $files = array();

        $files = AntTools::getFileList(antPluginPath, null, true);

        foreach ($files as $file) {
            if (substr($file, -10) === "Plugin.php") {
                include_once $file;
                $className = pathinfo($file, PATHINFO_FILENAME);
                $plugins[] = new $className();
            }
        }

        return $plugins;
    }
}
