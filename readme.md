![PHPStan Level](https://img.shields.io/badge/PHPStan-level%206-brightgreen)
[![CI](https://github.com/AntCMS-org/AntCMS/actions/workflows/ci.yml/badge.svg)](https://github.com/AntCMS-org/AntCMS/actions/workflows/ci.yml)
![Supported PHP Versions](https://img.shields.io/badge/PHP%20Versions-8.2%7C8.3%7C8.4-brightgreen)
![GitHub Release](https://img.shields.io/github/v/release/AntCMS-org/AntCMS)

# AntCMS

AntCMS is a flat-file CMS that's built to have very low system resource usage while providing website speeds rivaling a static website.

Although it is still considered preview software and is missing quite a bit of planned functionality, AntCMS in it's current state is both a functional and very fast CMS that can be used to deploy a website.

## Missing Functionality

AntCMS is being largely rebuilt for better, faser, easier functionality the following functionality currently does not exist compared to previous versions:

 - The administrator plugin (UI for system management).
 - User management.

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
