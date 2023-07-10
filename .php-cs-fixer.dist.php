<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->path(['src']);

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        'declare_strict_types' => true,
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => false,
        'no_trailing_whitespace_in_string' => false,
        'php_unit_internal_class' => false,
        'php_unit_test_annotation' => false,
        'php_unit_test_class_requires_covers' => false,
        'php_unit_strict' => false,
        'single_quote' => [
            'strings_containing_single_quote_chars' => true,
        ],
        'void_return' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'method_public_static',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private'
            ],
        ],
        'yoda_style' => false,
    ])
    ->setFinder($finder);
