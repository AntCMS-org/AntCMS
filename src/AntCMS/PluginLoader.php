<?php

namespace AntCMS;

use AntCMS\Tools;

class PluginLoader
{
    /** @return array<mixed>  */
    public function loadPlugins(): array
    {
        $plugins = [];

        $files = Tools::getFileList(antPluginPath, null, true);

        foreach ($files as $file) {
            if (str_ends_with($file, "Plugin.php")) {
                include_once Tools::repairFilePath($file);
                $className = pathinfo($file, PATHINFO_FILENAME);
                $plugins[] = new $className();
            }
        }

        return $plugins;
    }
}
