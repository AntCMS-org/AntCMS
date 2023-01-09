<?php

namespace AntCMS;

class AntTools
{
    public static function getFileList($dir, $extension = null, $returnPath = false)
    {
        $dir = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dir);
        $files = array();
        foreach ($iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension || $extension == null) {
                $files[] = ($returnPath) ? $file->getPathname() : $file->getFilename();
            }
        }
        return $files;
    }

    public static function repairFilePath($path)
    {
        $newPath = realpath($path);
        if (!$newPath) {
            $newPath = str_replace('//', '/', $path);
            $newPath = str_replace('\\\\', '/', $newPath);
            $newPath = str_replace('\\', '/', $newPath);
            $newPath = str_replace('/', DIRECTORY_SEPARATOR, $newPath);
        }

        return $newPath;
    }
}
