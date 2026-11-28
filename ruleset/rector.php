<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/tests/PHPUnit',
    ])
    ->withPHPStanConfigs([__DIR__ . '/phpstan.neon'])
    ->withPhpSets(php82: true)
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        doctrineCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withTypeCoverageLevel(0)
    // Symfony rules matching the installed Symfony version (versioned SymfonySetList constants were removed in Rector 2.x)
    ->withComposerBased(symfony: true);
