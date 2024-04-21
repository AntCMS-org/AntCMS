<?php

namespace AntCMS;

use HostByBelle\CompressionBuffer;
use Symfony\Contracts\Cache\ItemInterface;

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
     * @param string $url The URL to repair. Note: this function will not work correctly if the URL provided has its own protocol (like https://).
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

    /**
     * Automatically selects an ideal compression method for various types of assets.
     * Impliments caching to prevent repeat processing of assets.
     */
    public static function doAssetCompression(string $path): array
    {
        $cache = new Cache();
        $contents = file_get_contents($path);
        $encoding = 'identity';
        switch (pathinfo($path, PATHINFO_EXTENSION)) {
            case 'css':
            case 'html':
            case 'js':
            case 'xml':
            case 'md':
            case 'log':
            case 'json':
                CompressionBuffer::enable(); // We will use CompressionBuffer to handle text content
                $encoding = CompressionBuffer::getFirstMethodChoice();
                $cacheKey = $cache->createCacheKeyFile($path, "assetCompression-$encoding");
                $contents = $cache->get($cacheKey, fn (ItemInterface $item): string => CompressionBuffer::handler($contents));
        }
        CompressionBuffer::disable();
        return [$contents, $encoding];
    }

    public static function getContentType(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $type = match ($ext) {
            'html' => 'text/html',
            'htm' => 'text/html',
            'txt' => 'text/plain',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            default => mime_content_type($path),
        };
        if ($type === false) {
            $type = 'application/octet-stream';
        }
        return $type;
    }
}
