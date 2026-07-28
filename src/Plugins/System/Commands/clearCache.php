<?php

/**
 * Copyright 2026 AntCMS
 */

use Symfony\Component\Filesystem\Filesystem;

class clearCache extends Ahc\Cli\Input\Command
{
    public function __construct()
    {
        parent::__construct('clearCache', 'Clears the system cache.');
    }

    public function execute(): void
    {
        $color = new Ahc\Cli\Output\Color();
        $filesystem = new Filesystem();
        $filesystem->remove(PATH_CACHE);
        $filesystem->mkdir(PATH_CACHE, 0o755);

        echo $color->ok("Done!\n");
    }
}
