<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'src/Vendor',
        'src/Cache',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'group_import' => true,
        'no_unneeded_import_alias' => true,
        'single_import_per_statement' => false,
    ])
    ->setFinder($finder)
;