--AntCMS--
Title: AntCMS
Author: The AntCMS Team
Description: AntCMS is a very fast CMS which provides easy SEO, low system resource usage, and makes writing content easy utilizing markdown. 
NavItem: true
--AntCMS--

# AntCMS

AntCMS is a flat-file CMS that's built to have very low system resource usage while providing website speeds rivaling a static website.

## Features

 - Built in support for gzip, brotli, and zstd compression.
 - Automatic compression for text-based assets (JS, HTML, CSS, ect).
 - Automatic image compression for JPEG, JPG, PNG, and WEBP image formats.
 - Insanely fast with zero tuning.
 - Minimal load on system resources.
 - Easy SEO with automatic handling of the robots.txt and sitemap files.
 - Automatic usage of ETags when serving assets to allow client-side caching.
 - Support for custom themes.
 - Per-theme additional styles applied to markdown content.
 - Plugin suppport.
 - Uses Markdown to write content and YAML for the infrequently needed configuration setup.

## System Requirements

 - PHP 8.2 or greater
 - PHP Extensions: `curl`, `dom`, `mbstring`

## Extensions for Improved Performance

For improved performance, the following PHP extensions are suggested:

 - `zstd` for zstd compression.
 - `brotli` for brotli compression.
 - `zlib` for gzip / deflate compression.
 - `gd` for automatic image compression.
 - `opcache` as it optimizes all PHP applications & in AntCMS allows for a faster caching implementation to be used.
 - `ctype`
