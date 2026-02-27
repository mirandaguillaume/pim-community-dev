<?php

/**
 * Back-end tasks.
 * Replaces targets from the root Makefile: fix-cs-back, cache, vendor, database, workers.
 */

namespace back;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'back', name: 'fix-cs-back', description: 'Run php-cs-fixer auto-fix')]
function fixCsBack(): void
{
    \phpRun('vendor/bin/php-cs-fixer fix --config=.php_cs.php');
}

#[AsTask(namespace: 'back', name: 'cache', description: 'Clear and warm up Symfony cache')]
function cache(): void
{
    \dockerCompose('run --rm php rm -rf var/cache');
    \phpRun('bin/console cache:warmup');
}

#[AsTask(namespace: 'back', name: 'vendor', description: 'Install composer dependencies')]
function vendor(): void
{
    \phpRun('/usr/local/bin/composer validate --no-check-all --no-check-lock');
    \phpRun('-d memory_limit=4G /usr/local/bin/composer install');
}

#[AsTask(namespace: 'back', name: 'check-requirements', description: 'Check PIM system requirements')]
function checkRequirements(): void
{
    \phpRun('bin/console pim:installer:check-requirements');
}

#[AsTask(namespace: 'back', name: 'database', description: 'Drop and recreate database')]
function database(
    #[AsOption(description: 'Catalog fixture path for pim:installer:db')]
    string $catalog = '',
): void {
    \phpRun('bin/console doctrine:database:drop --force --if-exists');
    \phpRun('bin/console doctrine:database:create --if-not-exists');
    $catalogOption = $catalog !== '' ? '--catalog ' . $catalog : '';
    \phpRun('bin/console pim:installer:db ' . $catalogOption);
}

#[AsTask(namespace: 'back', name: 'start-job-worker', description: 'Start messenger consumer')]
function startJobWorker(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    \phpRun('bin/console messenger:consume ui_job import_export_job data_maintenance_job ' . $options);
}

#[AsTask(namespace: 'back', name: 'stop-workers', description: 'Stop all messenger workers')]
function stopWorkers(): void
{
    \phpRun('bin/console messenger:stop-workers');
}
