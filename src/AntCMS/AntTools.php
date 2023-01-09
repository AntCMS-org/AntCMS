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


/**
 * Repairs a URL by replacing backslashes with forward slashes and removing duplicate slashes.
 *
 * @param string $url The URL to repair. Note: this function will not work correctly if the URL provided has its own protocol (like HTTS://).
 * @return string The repaired URL
 */
public static function repairURL($url)
{
    $newURL = str_replace('\\\\', '/', $url);
    $newURL = str_replace('\\', '/', $newURL);
    $newURL = str_replace('//', '/', $newURL);

    return $newURL;
}

}
