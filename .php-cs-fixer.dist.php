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
        '@auto' => true,
        '@PER-CS' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'no_unneeded_import_alias' => true,
        'single_import_per_statement' => true,
        'array_push' => true,
        'modernize_strpos' => true,
        'attribute_empty_parentheses' => true,
        'no_empty_comment' => true,
        'single_line_comment_spacing' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'no_homoglyph_names' => true,
        'binary_operator_spaces' => ['default' => 'single_space'],
        'no_empty_statement' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => true],
        'explicit_string_variable' => true,
        'header_comment' => [
            'header' => 'Copyright 2025 AntCMS',
            'comment_type' => 'PHPDoc',
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
