<?php

namespace AntCMS;

class AntTools
{
    /**
     * @param string $dir 
     * @param (null|string)|null $extension 
     * @param null|bool $returnPath 
     * @return array<string> 
     */
    public static function getFileList(string $dir, ?string $extension = null, ?bool $returnPath = false)
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

    /**
     * @param string $path 
     * @return string 
     */
    public static function repairFilePath(string $path)
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
    public static function repairURL(string $url)
    {
        $newURL = str_replace('\\\\', '/', $url);
        $newURL = str_replace('\\', '/', $newURL);

        return str_replace('//', '/', $newURL);
    }

    public static function convertFunctionaltoFullpath(string $path)
    {
        $pagePath = AntTools::repairFilePath(antContentPath . '/' . $path);

        if (is_dir($pagePath)) {
            $pagePath .= '/index.md';
        }

        if (!str_ends_with($pagePath, ".md")) {
            $pagePath .= '.md';
        }

        return AntTools::repairFilePath($pagePath);
    }
}
