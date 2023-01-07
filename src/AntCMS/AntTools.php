<?php

namespace AntCMS;

class AntTools
{
    public static function getFileList($dir, $extension = null, $returnPath = false)
    {
        $dir = new \RecursiveDirectoryIterator(antThemePath);
        $iterator = new \RecursiveIteratorIterator($dir);
        foreach ($iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension || $extension == null) {
                $files[] = ($returnPath) ? $file->getPathname() : $file->getFilename();
            }
        }
        return $files;
    }
}
