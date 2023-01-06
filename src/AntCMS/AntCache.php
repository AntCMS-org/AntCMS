<?php

namespace AntCMS;

use AntCMS\AntConfig;

class AntCache
{
    public function setCache($key, $content)
    {
        $cachePath = AntCachePath . "/$key.cache";
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            try {
                $cache = fopen($cachePath, "w");
                fwrite($cache, (string)$content);
                fclose($cache);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    public function getCache($key)
    {
        $cachePath = AntCachePath . "/$key.cache";
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            try {
                $contents = file_get_contents($cachePath);
                return $contents;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    public function isCached($key)
    {
        $config = AntConfig::currentConfig();
        if ($config['enableCache']) {
            $cachePath = AntCachePath . "/$key.cache";
            return file_exists($cachePath);
        } else {
            return false;
        }
    }
}
