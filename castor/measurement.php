<?php

/**
 * Measurement bounded context tasks.
 * Replaces make-file/measurement.mk.
 */

namespace measurement;

use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'measurement', name: 'lint-back', description: 'Run Rector for measurement')]
function lintBack(): void
{
    \rector('src/Akeneo/Tool/Bundle/MeasureBundle/back/tests/rector.php');
}

#[AsTask(namespace: 'measurement', name: 'lint-fix-back', description: 'Auto-fix measurement with Rector')]
function lintFixBack(): void
{
    \rector('src/Akeneo/Tool/Bundle/MeasureBundle/back/tests/rector.php', dryRun: false);
}

#[AsTask(namespace: 'measurement', name: 'acceptance-back', description: 'Run acceptance tests for measurement')]
function acceptanceBack(): void
{
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php Akeneo_Measurement_Acceptance');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c . --testsuite Akeneo_Measurement_Acceptance');
    }
}
