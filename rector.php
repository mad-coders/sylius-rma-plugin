<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\CodeQuality\Rector\Class_\EventSubscriberToAttributeRector;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        typeDeclarations: true,
    )
    ->withComposerBased(symfony: true)
    ->withImportNames(importShortClasses: false)
    ->withSkip([
        __DIR__ . '/src/Migrations',
        // Services are wired in XML without autoconfigure (ADR 0003), so #[AsEventListener]
        // attributes would never be registered and the workflow callbacks would silently stop.
        EventSubscriberToAttributeRector::class,
    ]);
