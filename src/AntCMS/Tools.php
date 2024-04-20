<?php

namespace AntCMS;

class Tools
{
    /**
     * @return array<string>
     */
    public static function getFileList(string $dir, ?string $extension = null, ?bool $returnPath = false): array
    {
        $dir = new \RecursiveDirectoryIterator($dir);
        $iterator = new \RecursiveIteratorIterator($dir);
        $files = [];
        foreach ($iterator as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == $extension || $extension == null) {
                $files[] = ($returnPath) ? $file->getPathname() : $file->getFilename();
            }
        }

        return $files;
    }

    public static function repairFilePath(string $path): string
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
    public static function repairURL(string $url): string
    {
        $newURL = str_replace('\\\\', '/', $url);
        $newURL = str_replace('\\', '/', $newURL);

        return str_replace('//', '/', $newURL);
    }

    public static function convertFunctionaltoFullpath(string $path): string
    {
        $pagePath = Tools::repairFilePath(antContentPath . '/' . $path);

        if (is_dir($pagePath)) {
            $pagePath .= '/index.md';
        }

        if (!str_ends_with($pagePath, ".md")) {
            $pagePath .= '.md';
        }

        return Tools::repairFilePath($pagePath);
    }

    public static function valuesNotNull(array $required, array $actual): bool
    {
        foreach ($required as $key) {
            if (!key_exists($key, $actual) or is_null($actual[$key])) {
                return false;
            }
        }

        return true;
    }
}