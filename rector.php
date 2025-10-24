<?php

declare(strict_types=1);

/**
 * Copyright 2025 AntCMS
 */

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/src/Vendor')
    ->withSkipPath(__DIR__ . '/src/Cache')
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        earlyReturn: true,
        instanceOf: true,
        naming: true,
        privatization: true,
        typeDeclarations: true,
    )
    ->withPhpSets()
;
