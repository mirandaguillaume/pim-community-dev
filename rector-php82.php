<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
    ]);

    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/components',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/src/*/Resources',
        __DIR__ . '/src/*/*/Resources',
        __DIR__ . '/src/*/*/*/Resources',
    ]);

    $rectorConfig->importShortClasses(false);
};
