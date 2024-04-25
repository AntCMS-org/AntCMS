<?php

namespace AntCMS;

class Pages
{
    private static string $currentPage = "";

    private static function generatePageInfo(string $path): array
    {
        $contents = file_get_contents($path);
        $functionalPath = substr(str_replace(PATH_CONTENT, "", $path), 0, -3);
        if (str_ends_with($functionalPath, '/index')) {
            $functionalPath = substr($functionalPath, 0, -5);
        }
        return [
            'title' => AntCMS::getPageHeaders($contents)['title'],
            'realPath' => $path,
            'functionalPath' => $functionalPath,
            'url' => "//" . Tools::repairURL(baseUrl . $functionalPath),
            'active' => $functionalPath === self::$currentPage,
            'navItem' => true,
        ];
    }

    /**
     * @return mixed[]
     */
    private static function buildList(string $path = PATH_CONTENT): array
    {
        $result = [];
        $list = array_flip(scandir($path) ?: []);
        unset($list['.'], $list['..']);

        // Loop through each item and builds the list of pages. Directories will recursively call this function again.
        foreach (array_keys($list) as $key) {
            $currentPath = $path . DIRECTORY_SEPARATOR . $key;
            if (is_dir($currentPath)) {
                $directoryListing = self::buildList($currentPath);

                // Remove non markdown files
                foreach ($directoryListing as $subKey => $item) {
                    if (is_array($item)) {
                        continue;
                    }
                    if (is_dir($currentPath . DIRECTORY_SEPARATOR . $item)) {
                        continue;
                    }
                    if (str_ends_with($item, '.md')) {
                        continue;
                    }
                    unset($directoryListing[$subKey]);
                }

                // Skip directories that are empty
                if ($directoryListing === []) {
                    continue;
                }

                $key = ucfirst($key);

                // Finally append it to the end result
                $result[$key] = $directoryListing;
            } else {
                // Skip non markdown files
                if (!str_ends_with($currentPath, '.md')) {
                    continue;
                }

                $result[$key] = self::generatePageInfo($currentPath);
            }
        }

        // Finally sort it 1-9 and then a-z
        uksort($result, function ($a, $b): int|float {
            // Ensure index items come first
            if ($a === 'index.md') {
                return -1;
            }
            if ($b === 'index.md') {
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

    public static function getPages(string $currentPage = ''): array
    {
        self::$currentPage = $currentPage;

        $startTime = hrtime(true);
        $result = self::buildList();
        $elapsedTime = (hrtime(true) - $startTime) / 1e+6;
        error_log("Generating pages took $elapsedTime milliseconds");
        return $result;
    }
}
