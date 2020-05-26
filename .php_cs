<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'no_empty_phpdoc' => true,
        'ordered_imports' => true,
        'protected_to_private' => false,
        'php_unit_test_class_requires_covers' => false,
        'no_superfluous_phpdoc_tags' => true,
        // adds : void to each function with no return value
        'void_return' => true,
     ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
        ->files()
        ->in(__DIR__ . '/samples')
        ->in(__DIR__ . '/src')
        ->name('*.php')
    );