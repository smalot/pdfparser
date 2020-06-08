<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_empty_phpdoc' => true,
        'no_unused_imports' => true,
        'no_superfluous_phpdoc_tags' => true,
        'ordered_imports' => true,
        'phpdoc_summary' => false,
        'protected_to_private' => false,
     ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude([
                'vendor',
            ])
            ->in(__DIR__)
            ->name('*.php')
    );
