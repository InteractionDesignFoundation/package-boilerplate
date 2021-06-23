<?php declare(strict_types=1);
return (new PhpCsFixer\Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setRules(
        [
            'align_multiline_comment' => ['comment_type' => 'phpdocs_only'],
            'array_indentation' => true,
            'class_attributes_separation' => ['elements' => ['method' => 'one']],
            'binary_operator_spaces' => ['default' => 'single_space'],

            'general_phpdoc_annotation_remove' => [
                'annotations' => [
                    'api',
                    'access',
                    'author',
                    'category',
                    'copyright',
                    'created',
                    'license',
                    'link',
                    'package',
                    'since',
                    'subpackage',
                    'version',
                ],
            ],
            'phpdoc_align' => ['align' => 'left'],
            'phpdoc_indent' => true,
            'phpdoc_scalar' => true,
            'phpdoc_single_line_var_spacing' => true,
            'no_empty_phpdoc' => true,
            'no_unused_imports' => true,

            'modernize_types_casting' => true,

            'php_unit_method_casing' => ['case' => 'snake_case'],
            'php_unit_test_annotation' => ['style' => 'annotation'],
            'php_unit_construct' => true,
            'php_unit_set_up_tear_down_visibility' => true,

            'ternary_to_null_coalescing' => true,
            'trim_array_spaces' => true,
        ]
    )
    ->setIndent('    ')
    ->setLineEnding("\n")
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude(
                [
                    '.docker',
                    'vendor',
                ]
            )
            ->name('*.php')
            ->notName('_ide_helper.php')
            ->notName('.phpstorm.meta.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
    );
