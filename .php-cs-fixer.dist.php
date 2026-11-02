<?php
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('doc')
    ->exclude('font')
    ->exclude('makefont')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules(['@PSR12' => true, 'control_structure_braces' => true])
    ->setFinder($finder);