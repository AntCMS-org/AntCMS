<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassLike\RemoveAnnotationRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $rectorConfig->skip([
        __DIR__ . '/src/Vendor',
        __DIR__ . '/src/Cache',
        UnionTypesRector::class,
        MixedTypeRector::class,
        EncapsedStringsToSprintfRector::class,
        ConsistentPregDelimiterRector::class,
        RemoveAnnotationRector::class,
    ]);

    $rectorConfig->sets([
        SetList::PHP_80,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::NAMING,
        SetList::DEAD_CODE,
    ]);
};
