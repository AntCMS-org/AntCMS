<?php

namespace AntCMS;

use AntCMS\AntTools;

class AntPluginLoader
{
    /** @return array<mixed>  */
    public function loadPlugins()
    {
        $plugins = array();

        $files = AntTools::getFileList(antPluginPath, null, true);

        foreach ($files as $file) {
            if (str_ends_with($file, "Plugin.php")) {
                include_once AntTools::repairFilePath($file);
                $className = pathinfo($file, PATHINFO_FILENAME);
                $plugins[] = new $className();
            }
        }

        return $plugins;
    }
}
