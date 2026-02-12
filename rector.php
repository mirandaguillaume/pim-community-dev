<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

/**
 * Rector configuration for Doctrine DBAL 2.x → 3.x migration.
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

    // DBAL 2.x → 3.x migration sets (incremental)
    $rectorConfig->sets([
        DoctrineSetList::DOCTRINE_DBAL_210,
        DoctrineSetList::DOCTRINE_DBAL_211,
        DoctrineSetList::DOCTRINE_DBAL_30,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
