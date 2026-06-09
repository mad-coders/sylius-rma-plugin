<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Sylius\SyliusRector\SetProvider\SyliusSetProvider;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    )
    ->withSetProviders(SyliusSetProvider::class)
    ->withComposerBased(symfony: true)
    ->withImportNames(importShortClasses: false)
    ->withSkip([
        __DIR__ . '/src/Migrations',
    ]);
