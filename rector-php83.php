<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;

/**
 * Rector config for PHP 8.3 feature adoption.
 *
 * Scope: #[\Override] attribute + typed class constants.
 * These rules add metadata/types without changing behavior.
 *
 * Usage:
 *   docker-compose run --rm php php vendor/bin/rector process --dry-run --config=rector-php83.php
 *   docker-compose run --rm php php vendor/bin/rector process --config=rector-php83.php
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
    ]);

    $rectorConfig->skip([
        // Test directories
        __DIR__ . '/tests',
        '*/tests/*',
        '*/Test/*',
        '*/spec/*',
        '*/Spec/*',
        '*/Specification/*',

        // Generated / vendored
        '*/var/*',
        '*/vendor/*',
        '*/node_modules/*',
    ]);

    $rectorConfig->rules([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        AddTypeToConstRector::class,
    ]);

    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
