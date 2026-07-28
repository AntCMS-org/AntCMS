<?php

/**
 * Copyright 2026 AntCMS
 */

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class initExample extends Ahc\Cli\Input\Command
{
    public function __construct()
    {
        parent::__construct('initExample', 'Downloads example content from GitHub and loads it into AntCMS. !! DELETES EXISTING CONTENT !!');
    }

    public function execute(): void
    {
        $filesystem = new Filesystem();

        // Download the repo and write it to a temp file
        $zipUrl = "https://github.com/AntCMS-org/Example-Content/archive/refs/heads/main.zip";
        $tempZip = $filesystem->tempnam(PATH_CACHE, 'githubsync_', '.zip');

        $zipData = file_get_contents($zipUrl);
        if ($zipData === false) {
            throw new \RuntimeException('Failed to download repository ZIP');
        }

        $filesystem->dumpFile($tempZip, $zipData);

        // Verify we can open the zip file
        $zipArchive = new \ZipArchive();
        if ($zipArchive->open($tempZip) !== true) {
            throw new \RuntimeException('Failed to open ZIP file');
        }

        // If we can open it, extract it and delete the temporary zip file
        $tmpExtract = Path::join(PATH_CACHE, 'githubsync_extract_', uniqid());
        $filesystem->mkdir($tmpExtract, 0o775);
        $zipArchive->extractTo($tmpExtract);
        $zipArchive->close();
        $filesystem->remove($tempZip);

        // Verify expected zip structure
        $rootDir = glob($tmpExtract . '/*', GLOB_ONLYDIR)[0] ?? null;
        if (!$rootDir) {
            $filesystem->remove($tmpExtract);
            throw new \RuntimeException('Unexpected ZIP structure');
        }

        // Finally if all checks passed, we can empty the destination folder
        $filesystem->remove(PATH_CONTENT);
        $filesystem->mkdir(PATH_CONTENT, 0o755);

        // Move contents to targetDir and perform cleanup
        $filesystem->rename($rootDir, PATH_CONTENT, true);
        $filesystem->remove($tmpExtract);
        $filesystem->remove(PATH_CONTENT . DIRECTORY_SEPARATOR . "README.md");
    }
}
