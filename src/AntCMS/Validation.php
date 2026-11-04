<?php

declare(strict_types=1);

/**
 * Copyright 2026 AntCMS
 */

namespace AntCMS;

class Validation
{
    /**
     * Check if an asset path is public.
     *
     * @param string $path The path to check
     * @return bool True if the path points to assets inside either the default or active theme
     */
    public static function isAssetPathPublic(string $path): bool
    {
        $path = realpath($path);
        if ($path === false) {
            return false;
        }
        return (str_starts_with($path, PATH_CURRENT_THEME . '/Assets/') || str_starts_with($path, PATH_THEMES . '/Default/Assets/'));
    }

    /**
     * Validate if a path lands under the content folder
     *
     * @param string $path The content path to validate
     * @return bool True if the path is .md file or directory under the content folder
     */
    public static function isValidContentPath(string $path): bool
    {
        $path = realpath($path);
        if ($path === false || !str_starts_with($path, PATH_CONTENT . DIRECTORY_SEPARATOR)) {
            return false;
        }
        return is_dir($path) || str_ends_with($path, '.md');
    }
}
