<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->exclude('var')
    ->in(__DIR__.'/src/symfony')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder)
;
