<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\GetFunctionsToAsTwigFunctionAttributeRector;
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
        // Targets the Symfony 7.3 twig-bridge, which auto-registers plain #[AsTwigFunction]
        // services without ExtensionInterface. This plugin supports Sylius 1.12/1.13, both on
        // Symfony 6.4's twig-bridge, which does not: applying it throws
        // "addExtension(): must be of type ExtensionInterface" at runtime (confirmed via Behat).
        GetFunctionsToAsTwigFunctionAttributeRector::class,
    ]);
