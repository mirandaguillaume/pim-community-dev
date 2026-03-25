<?php

/**
 * Code quality audit tasks: mutation testing, etc.
 */

namespace quality;

use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'quality', name: 'mutation-back', description: 'Run Infection PHP mutation testing (full audit)')]
function mutationBack(string $filter = '', bool $onlyCovered = true, string $threads = 'max'): void
{
    \ensureDir('var/infection');

    // Step 1: generate coverage (Infection needs it to map tests → source files)
    echo "Generating PHPUnit coverage for Infection...\n";
    run(
        'APP_ENV=test_fake docker-compose run --rm'
        . ' -e XDEBUG_MODE=coverage'
        . ' php php vendor/bin/phpunit -c .'
        . ' --testsuite PHPUnit_Unit_Test'
        . ' --coverage-xml var/infection/coverage-xml'
        . ' --log-junit var/infection/junit.xml',
    );

    // Step 2: run Infection with pre-generated coverage
    $cmd = 'vendor/bin/infection'
        . ' --threads=' . $threads
        . ' --coverage=var/infection'
        . ' --skip-initial-tests'
        . ' --show-mutations'
        . ' --no-interaction';

    if ($onlyCovered) {
        $cmd .= ' --only-covered';
    }

    if ('' !== $filter) {
        $cmd .= ' --filter=' . escapeshellarg($filter);
    }

    echo "Running Infection mutation testing...\n";
    \phpRun($cmd);

    echo "\nReports saved to var/infection/\n";
}

#[AsTask(namespace: 'quality', name: 'mutation-front', description: 'Run Stryker JS/TS mutation testing (full audit)')]
function mutationFront(): void
{
    \ensureDir('var/stryker');

    echo "Running Stryker mutation testing...\n";
    \yarnRun('mutation-front');

    echo "\nReports saved to var/stryker/\n";
}
