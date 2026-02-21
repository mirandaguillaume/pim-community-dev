<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony\Set\SymfonySetList;

/**
 * Rector configuration for Symfony 5.4 → 6.4 upgrade preparation.
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

        // Rector bug: MessageSubscriberInterfaceToAttributeRector crashes on this file
        Rector\Symfony\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector::class => [
            __DIR__ . '/src/Akeneo/Connectivity/Connection/back/Infrastructure/Webhook/MessageHandler/BusinessEventHandler.php',
        ],
    ]);

    // Doctrine Persistence 2 → 3 migration
    $rectorConfig->sets([
        DoctrineSetList::DOCTRINE_COMMON_20,
        DoctrineSetList::DOCTRINE_ORM_213,
    ]);

    // Symfony 5.4 → 6.2 deprecation fixes (preparing for 6.4 upgrade)
    $rectorConfig->sets([
        SymfonySetList::SYMFONY_54,   // Annotations, listener factories
        SymfonySetList::SYMFONY_60,   // getUsername → getUserIdentifier, loadUserByUsername → loadUserByIdentifier
        SymfonySetList::SYMFONY_61,   // $defaultName → #[AsCommand]
        SymfonySetList::SYMFONY_62,   // Security\Core\Security → SecurityBundle\Security
    ]);

    // Don't auto-import names to avoid cosmetic changes across the codebase
    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
