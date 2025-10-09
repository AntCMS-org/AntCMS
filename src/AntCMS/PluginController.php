<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class PluginController
{
    /** @var string[] */
    private static array $plugins = [];

    /** @var array<string, string[]> */
    private static array $sitemapUrls = [];

    /** @var array<string, string[]> */
    private static array $robotsTxtAdditions = [
        'allow' => [],
        'disallow' => [],
    ];

    /**
     * Registers all plugin routes & sets up needed info for the plugin controller
     */
    public static function init(): void
    {
        $finder = Finder::create()->in(PATH_PLUGINS)->directories()->depth("<1");

        foreach ($finder as $dir) {
            $pluginName = $dir->getFilename();
            $className = "\AntCMS\\Plugins\\{$pluginName}\\Controller";
            if (!class_exists($className)) {
                error_log("Plugin class {$className} does not exist, plugin cannot be loaded.");
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
            $templateDir = Path::join(PATH_PLUGINS, $pluginName, 'Templates');
            if (is_dir($templateDir)) {
                Twig::addLoaderPath($templateDir);
            }
        }

        HookController::fire('onAfterPluginsInit');
    }

    /**
     * @return string[]
     */
    public static function getLoadedPlugins(): array
    {
        return self::$plugins;
    }

    /**
     * @return array<string, string[]>
     */
    public static function getSitemapUrls(): array
    {
        return self::$sitemapUrls;
    }

    /**
     * @return array<string, string[]>
     */
    public static function getRobotsTxtEntries(): array
    {
        return self::$robotsTxtAdditions;
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
        self::$robotsTxtAdditions['allow'][] = $url;
    }

    public static function addDisallowToRobotsTxt(string $url): void
    {
        self::$robotsTxtAdditions['disallow'][] = $url;
    }
}
