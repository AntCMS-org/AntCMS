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

        // Skip compression when asset compression is disabled
        if (!compressTextAssets) {
            return [$contents, $encoding];
        }

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
            'html', 'htm' => 'text/html',
            'txt' => 'text/plain',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'jpg', 'jpeg' => 'image/jpeg',
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

    private static function createDebugLogLine(string $wording, bool|string $value): string
    {
        if (is_bool($value)) {
            $value = $value ? "enabled" : "disabled";
        }
        return "<dd>$wording: <strong>$value</strong></dd>";
    }

    public static function buildDebugInfo(): string
    {
        $elapsed_time = round((hrtime(true) - START) / 1e+6, 2);
        $mem_usage = round(memory_get_peak_usage() / 1e+6, 2);

        // Performance info
        $result = "<dl><dt>Performance Metrics</dt>";
        $result .= self::createDebugLogLine('Time to process request', "$elapsed_time ms");
        $result .= self::createDebugLogLine('Memory usage', "$mem_usage MB");

        // System info
        $result .= "<dt>System Info</dt>";
        $result .= self::createDebugLogLine('Output compression', doOutputCompression);

        if (CompressionBuffer::isEnabled() && doOutputCompression) {
            $method = CompressionBuffer::getFirstMethodChoice();
            if ($method === 'br') {
                $method = 'brotli';
            }
            $result .= self::createDebugLogLine('Compression method', $method);
        } else {
            $result .= self::createDebugLogLine('Output compression', 'disabled');
        }

        $result .= self::createDebugLogLine('Asset compression', compressTextAssets);
        $result .= self::createDebugLogLine('PHP version', PHP_VERSION);

        return $result . "</dl>";
    }
}
