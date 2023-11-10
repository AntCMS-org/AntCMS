<?php

namespace AntCMS;

use AntCMS\AntTools;
use Slim\App;

class AntPluginLoader
{
    /** @return array<mixed>  */
    public function loadPlugins()
    {
        $plugins = [];

        $files = AntTools::getFileList(antPluginPath, 'php', true);

        foreach ($files as $file) {
            if (str_ends_with($file, "Plugin.php")) {
                include_once AntTools::repairFilePath($file);
                $className = pathinfo($file, PATHINFO_FILENAME);
                $plugins[] = new $className();
            }
        }

        return $plugins;
    }

    public function registerPluginRoutes(App $app)
    {
        $files = scandir(antPluginPath);
        foreach ($files as $file) {
            $fqcn = '\\Plugins\\' . $file . '\\Controller';
            if (class_exists($fqcn)) {
                $controler = new $fqcn;
                if (method_exists($controler, 'registerRoutes')) {
                    $controler->registerRoutes($app);
                }
            }
        }
    }
}
