<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/tests',
        '*/tests/*',
        '*/Test/*',
        '*/spec/*',
        '*/Spec/*',
        '*/Specification/*',
        '*/var/*',
        '*/vendor/*',
        '*/node_modules/*',
    ]);

    $rectorConfig->sets([
        SetList::DEAD_CODE,
    ]);

    $rectorConfig->importNames(false);
    $rectorConfig->importShortClasses(false);
};
