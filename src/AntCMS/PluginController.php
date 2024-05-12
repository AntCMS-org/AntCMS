<?php

namespace AntCMS;

class PluginController
{
    private static array $plugins = [];
    private static array $sitemapUrls = [];
    private static array $robotsTxtAdditons = [
        'allow' => [],
        'disallow' => [],
    ];

    /**
     * Registers all plugin routes & sets up needed info for the plugin controller
     */
    public static function init(): void
    {
        $list = scandir(PATH_PLUGINS);
        if (count($list) >= 2 && $list[0] === '.' && $list[1] === '..') {
            unset($list[0]);
            unset($list[1]);
        }

        foreach ($list as $pluginName) {
            $className = "\AntCMS\\Plugins\\$pluginName\\Controller";
            if (!class_exists($className)) {
                error_log("Plugin class $className does not exist, plugin cannot be loaded.");
                continue;
            }

            // Ensure we don't accidentally load a plugin multiple times
            if (in_array($pluginName, self::$plugins)) {
                continue;
            }

            self::$plugins[] = $pluginName;

            // Create the class, let the constructor register any routes needed
            new $className();

            // Register templates for the plugin
            $templateDir = PATH_PLUGINS . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . 'Templates';
            if (is_dir($templateDir)) {
                Twig::addLoaderPath($templateDir);
            }
        }
    }

    public static function getLoadedPlugins(): array
    {
        return self::$plugins;
    }

    public static function getSitemapUrls(): array
    {
        return self::$sitemapUrls;
    }

    public static function getRobotsTxtEntries(): array
    {
        return self::$robotsTxtAdditons;
    }

    public static function registerSitemapUrl(string $url, ?int $lastmod = null): void
    {
        self::$sitemapUrls[] = [
            'url' => $url,
            'lastmod' => $lastmod,
        ];
    }

    public static function addAllowToRobotsTxt(string $url): void
    {
        self::$robotsTxtAdditons['allow'][] = $url;
    }

    public static function addDisallowToRobotsTxt(string $url): void
    {
        self::$robotsTxtAdditons['disallow'][] = $url;
    }
}
