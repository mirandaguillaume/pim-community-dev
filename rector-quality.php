<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

/**
 * Rector config for PHP code quality improvements.
 *
 * Sets: CODE_QUALITY, DEAD_CODE, TYPE_DECLARATION
 * Excluded: NAMING (renames public API → breaks plugins), EARLY_RETURN (changes execution order)
 *
 * Usage:
 *   docker-compose run --rm php php -d memory_limit=4G vendor/bin/rector process --dry-run --config=rector-quality.php
 *   docker-compose run --rm php php -d memory_limit=4G vendor/bin/rector process --config=rector-quality.php
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
    ]);

    $rectorConfig->skip([
        // Test directories (different code style expectations)
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

        // InlineConstructorDefaultToPropertyRector conflicts with readonly classes
        InlineConstructorDefaultToPropertyRector::class,
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
    ]);

    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
