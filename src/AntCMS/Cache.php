<?php

namespace AntCMS;

class AntCache
{
    public function setCache($key, $content)
    {
        $cachePath = AntCache . "/$key.cache";
        try {
            $cache = fopen($cachePath, "w");

            fwrite($cache, (string)$content);
            fclose($cache);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCache($key)
    {
        $cachePath = AntCache . "/$key.cache";
        try {
            $contents = file_get_contents($cachePath);
            return $contents;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isCached($key)
    {
        $cachePath = AntCache . "/$key.cache";
        return file_exists($cachePath);
    }
}
