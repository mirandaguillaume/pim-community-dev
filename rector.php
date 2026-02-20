<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

/**
 * Rector configuration for Doctrine migration.
 *
 * Usage:
 *   vendor/bin/rector process --dry-run
 *   vendor/bin/rector process
 *   vendor/bin/rector process src/Akeneo/Pim/Enrichment --dry-run
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
        __DIR__ . '/upgrades',
    ]);

    $rectorConfig->skip([
        // Test directories
        __DIR__ . '/src/*/Bundle/*/Tests',
        __DIR__ . '/src/*/*/tests',
        __DIR__ . '/src/*/*/*/tests',
        __DIR__ . '/src/*/*/*/*/tests',
        __DIR__ . '/tests',

        // Spec files
        '*/spec/*',
        '*/Spec/*',

        // Generated / vendored
        '*/var/*',
        '*/vendor/*',
    ]);

    // Doctrine Persistence 2 → 3 migration
    $rectorConfig->sets([
        DoctrineSetList::DOCTRINE_COMMON_20,   // Doctrine\Common\Persistence\* → Doctrine\Persistence\*
        DoctrineSetList::DOCTRINE_ORM_213,     // ORM event args → Persistence event args
    ]);

    // Don't auto-import names to avoid cosmetic changes across the codebase
    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
