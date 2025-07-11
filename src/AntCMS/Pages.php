<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

class Pages
{
    private static string $currentPage = "";

    /**
     * @return array<string, mixed>
     */
    private static function generatePageInfo(string $path): array
    {
        $contents = file_get_contents($path);
        $functionalPath = substr(str_replace(PATH_CONTENT, "", $path), 0, -3);
        if (str_ends_with($functionalPath, '/index')) {
            $functionalPath = substr($functionalPath, 0, -5);
        }

        $pageHeader = AntCMS::getPageHeaders($contents);

        return [
            'title' => $pageHeader['title'],
            'realPath' => $path,
            'functionalPath' => $functionalPath,
            'url' => "//" . Tools::repairURL(BASE_URL . $functionalPath),
            'active' => $functionalPath === self::$currentPage,
            'navItem' => $pageHeader['NavItem'] !== 'false',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function getDirectoryMeta(string $path): array
    {
        $metaPath = $path . DIRECTORY_SEPARATOR . 'meta.yaml';
        $result = [
            'title' => ucfirst(basename($path)),
            'pageOrder' => [],
        ];

        if (file_exists($metaPath)) {
            try {
                $directoryMetaData = AntYaml::parseFile($metaPath);
                $result = array_merge($result, $directoryMetaData);
            } catch (\Exception $e) {
                error_log("Error while loading the meta data for the $path directory:");
                error_log("YAML error: " . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * @return mixed[]
     */
    private static function buildList(string $path = PATH_CONTENT): array
    {
        $result = [];
        $list = array_flip(scandir($path) ?: []);
        unset($list['.'], $list['..']);
        $directoryMeta = self::getDirectoryMeta($path);

        // Loop through each item and builds the list of pages. Directories will recursively call this function again.
        foreach (array_keys($list) as $key) {
            $currentPath = $path . DIRECTORY_SEPARATOR . $key;
            if (is_dir($currentPath)) {
                $subDirectoryMeta = self::getDirectoryMeta($currentPath);
                $directoryListing = self::buildList($currentPath);

                // Remove non markdown files
                foreach ($directoryListing as $subKey => $item) {
                    if (is_array($item)) {
                        continue;
                    }
                    if (is_dir($currentPath . DIRECTORY_SEPARATOR . $item)) {
                        continue;
                    }
                    if (str_ends_with((string) $item, '.md')) {
                        continue;
                    }
                    unset($directoryListing[$subKey]);
                }

                // Skip directories that are empty
                if ($directoryListing === []) {
                    continue;
                }

                // Finally append it to the end result
                $result[$subDirectoryMeta['title']] = $directoryListing;
            } else {
                // Skip non markdown files
                if (!str_ends_with($currentPath, '.md')) {
                    continue;
                }

                $key = substr($key, 0, -3);

                $result[$key] = self::generatePageInfo($currentPath);
            }
        }

        // Finally sort it 1-9 and then a-z
        uksort($result, function ($a, $b) use ($directoryMeta): int|float {
            // Respect the user provided order
            if (isset($directoryMeta['pageOrder'][$a]) && isset($directoryMeta['pageOrder'][$b])) {
                return $directoryMeta['pageOrder'][$a] > $directoryMeta['pageOrder'][$b] ? 1 : -1;
            }
            if (isset($directoryMeta['pageOrder'][$a]) && !isset($directoryMeta['pageOrder'][$b])) {
                return -1;
            }
            if (!isset($directoryMeta['pageOrder'][$a]) && isset($directoryMeta['pageOrder'][$b])) {
                return 1;
            }
            // Ensure index items come first
            if ($a === 'index') {
                return -1;
            }
            if ($b === 'index') {
                return 1;
            }
            if (is_numeric($a) && is_numeric($b)) {
                return $a - $b;
            }
            if (is_numeric($a)) {
                return -1;
            }
            if (is_numeric($b)) {
                return 1;
            }
            return strcasecmp($a, $b);
        });

        return $result;
    }

    /**
     * @return mixed[]
     */
    public static function getPages(string $currentPage = ''): array
    {
        self::$currentPage = $currentPage;
        return self::buildList();
    }
}
