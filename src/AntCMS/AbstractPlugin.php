<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS;

abstract class AbstractPlugin
{
    /**
     * All plugins must impliment a construct function to then register any hooks or routes
     */
    abstract public function __construct();

    /**
     * Appends a URL to the sitemap.xml file.
     *
     * @param string $url The relative URL to append.
     * @param int|null $lastmod A unix timestamp representing when this item was last modified. Passing null excludes this from the sitemap.
     */
    public function appendSitemap(string $url, ?int $lastmod = null): void
    {
        PluginController::registerSitemapUrl($url, $lastmod);
    }

    /**
     * Adds an allow entry to the robots.txt file.
     *
     * @param string $url the URL to allow
     */
    public function addAllow(string $url): void
    {
        PluginController::addAllowToRobotsTxt($url);
    }

    /**
     * Adds a disallow entry to the robots.txt file.
     *
     * @param string $url the URL to disallow
     */
    public function addDisallow(string $url): void
    {
        PluginController::addDisallowToRobotsTxt($url);
    }
}
