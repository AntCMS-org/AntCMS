<?php

/**
 * Copyright 2026 AntCMS
 */

use AntCMS\Cache;
use AntCMS\Markdown;
use AntCMS\PluginController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class precompute extends Ahc\Cli\Input\Command
{
    public function __construct()
    {
        parent::__construct('precompute', 'Pre-computes and caches markdown to always ensure near-instant access.');
    }

    public function execute(): void
    {
        PluginController::init();
        $color = new Ahc\Cli\Output\Color();
        $finder = new Finder();
        $filesystem = new Filesystem();

        $finder->files()->in([PATH_CONTENT, PATH_PLUGINS])->name("*.md");

        // check if there are any search results
        if (!$finder->hasResults()) {
            echo $color->info("There's nothing to do!\n");
        }

        foreach ($finder as $file) {
            echo $color->info("Precomputing {$file}\n");
            $pageContent = $filesystem->readFile($file);
            $pageContent = preg_replace('/\A--AntCMS--.*?--AntCMS--/sm', '', $pageContent);
            $cacheKey = Cache::createCacheKeyFile($file, 'content');
            Markdown::parse($pageContent, $cacheKey);
        }

        echo $color->ok("Done!\n");
    }
}
