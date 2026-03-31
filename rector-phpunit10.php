<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

/**
 * Rector config for PHPUnit 10 attribute adoption.
 *
 * Migrates @test → #[Test], @dataProvider → #[DataProvider], etc.
 * These are annotation-to-attribute conversions with no behavioral change.
 *
 * Usage:
 *   docker-compose run --rm php php vendor/bin/rector process --dry-run --config=rector-phpunit10.php
 *   docker-compose run --rm php php vendor/bin/rector process --config=rector-phpunit10.php
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/components',
    ]);

    $rectorConfig->skip([
        '*/var/*',
        '*/vendor/*',
        '*/node_modules/*',
    ]);

    $rectorConfig->sets([
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);

    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
