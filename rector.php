<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkipPath(__DIR__ . '/src/Vendor')
    ->withDeadCodeLevel(42)
    ->withTypeCoverageLevel(20)
    ->withPhpSets();
