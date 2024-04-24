<?php

namespace AntCMS;

use HostByBelle\CompressionBuffer;
use Symfony\Contracts\Cache\ItemInterface;

class Tools
{
    private static array $textAssets = [
        'css',
        'html',
        'js',
        'xml',
        'md',
        'log',
        'json',
    ];

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
                $files[] = ($returnPath === true) ? $file->getPathname() : $file->getFilename();
            }
        }

        return $files;
    }

    public static function repairFilePath(string $path): string
    {
        $newPath = realpath($path);
        if ($newPath === false) {
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
            if (!array_key_exists($key, $actual) || is_null($actual[$key])) {
                return false;
            }
        }

        return true;
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
            return 'application/octet-stream';
        }
        return $type;
    }

    private static function isCompressableTextAsset(string $path): bool
    {
        return in_array(pathinfo($path, PATHINFO_EXTENSION), self::$textAssets) || str_starts_with(self::getContentType($path), 'text/');
    }

    private static function isCompressableImage(string $path): bool
    {
        // We require GD to perform image compression / detect the image type
        if (!extension_loaded('gd')) {
            return false;
        }

        $result = getimagesize($path);
        $gdInfo = gd_info();

        if ($result === false) {
            return false;
        }

        return match ($result['mime']) {
            'image/jpeg', 'image/jpg' => isset($gdInfo['JPEG Support']) && $gdInfo['JPEG Support'],
            'image/png' => isset($gdInfo['PNG Support']) && $gdInfo['PNG Support'],
            'image/webp' => isset($gdInfo['WebP Support']) && $gdInfo['WebP Support'],
            default => false,
        };
    }

    private static function compressImage(string $path, int $quality = 85): string
    {
        // Get the image type
        $imageInfo = getimagesize($path);
        $original = file_get_contents($path);
        $startingSize = strlen($original);

        if ($imageInfo === false) {
            return $original;
        }

        // Create image based on type
        $image = match ($imageInfo['mime']) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };

        // Compress the image and capture the output generated by GD.
        ob_start();
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($image, null, $quality);
                break;
            case 'image/png':
                imagepng($image, null, intval(round(9 * ($quality - 15) / 100)));
                break;
            case 'image/webp':
                imagewebp($image, null, $quality);
                break;
            default:
                echo $original;
        }

        $result = ob_get_clean();

        // Ensure we return the original if it's size went up
        if (strlen($result) >= $startingSize) {
            return $original;
        }
        return $result;
    }


    public static function getAssetCacheKey(string $path): string
    {
        $encoding = CompressionBuffer::getFirstMethodChoice();
        if (compressTextAssets && self::isCompressableTextAsset($path)) {
            return Cache::createCacheKeyFile($path, "assetCompression-$encoding");
        }
        return Cache::createCacheKeyFile($path, 'asset');
    }

    /**
     * Automatically selects an ideal compression method for various types of assets.
     * Impliments caching to prevent repeat processing of assets.
     */
    public static function doAssetCompression(string $path): array
    {
        $cache = new Cache();
        $cacheKey = self::getAssetCacheKey($path);
        $encoding = 'identity';

        if (compressTextAssets && self::isCompressableTextAsset($path)) {
            CompressionBuffer::enable(); // We will use CompressionBuffer to handle text content
            $encoding = CompressionBuffer::getFirstMethodChoice();

            $contents = $cache->get($cacheKey, function (ItemInterface $item) use ($path): string {
                $item->expiresAfter(604800);
                $contents = file_get_contents($path);
                return CompressionBuffer::handler($contents);
            });
        }

        if (compressImageAssets && self::isCompressableImage($path)) {
            $contents = $cache->get($cacheKey, function (ItemInterface $item) use ($path): string {
                $item->expiresAfter(604800);
                return self::compressImage($path);
            });
        }

        // Ensure CompressionBuffer won't accidentally cause issues for us
        CompressionBuffer::disable();

        // Handle cases were we didn't do compression or an error occured
        if (!isset($contents) || !is_string($contents)) {
            return [file_get_contents($path), 'identity'];
        }

        return [$contents, $encoding];
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
            $result .= self::createDebugLogLine('This page was compressed with', $method);
        }

        $result .= self::createDebugLogLine('Asset compression', compressTextAssets);
        $result .= self::createDebugLogLine('Image compression', compressImageAssets);
        $result .= self::createDebugLogLine('PHP version', PHP_VERSION);

        return $result . "</dl>";
    }
}
