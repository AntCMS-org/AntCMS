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
        parent::__construct('initExample', 'Downloads example content from GitHub and loads it into AntCMS.');
    }

    public function execute(): void
    {
        $interactor = new Ahc\Cli\IO\Interactor();
        $filesystem = new Filesystem();
        $color = new Ahc\Cli\Output\Color();

        $confirm = $interactor->confirm('This will delete everything under the content directory and replace it with the example content found under the AntCMS GitHub Organization. Proceed?', 'n'); // Default: n (no)

        if (!$confirm) {
            echo $color->warn("User cancelled command.\n");
            return;
        }

        echo $color->comment("Beginning download.\n");

        // Download the repo and write it to a temp file
        $zipUrl = "https://github.com/AntCMS-org/Example-Content/archive/refs/heads/main.zip";
        $tempZip = $filesystem->tempnam(PATH_CACHE, 'exampleContent_', '.zip');

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

        echo $color->comment("Extracting archive.\n");

        // If we can open it, extract it and delete the temporary zip file
        $tmpExtract = Path::join(PATH_CACHE, 'exampleContent_', uniqid());
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

        echo $color->warn("Warning: In 5 seconds the content folder will be emptied!\n");
        sleep(5);
        // Finally if all checks passed, we can empty the destination folder
        $filesystem->remove(PATH_CONTENT);
        $filesystem->mkdir(PATH_CONTENT, 0o755);

        echo $color->comment("Moving example content to final destination and performing clean-up.\n");
        // Move contents to targetDir and perform cleanup
        $filesystem->rename($rootDir, PATH_CONTENT, true);
        $filesystem->remove($tmpExtract);
        $filesystem->remove(PATH_CONTENT . DIRECTORY_SEPARATOR . "README.md");

        echo $color->ok("Done!\n");
    }
}
