<?php

/**
 * Job bounded context tasks.
 * Replaces make-file/job.mk.
 */

namespace job;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'job', name: 'lint-back', description: 'Run PHPStan, cs-fixer and Rector for job')]
function lintBack(): void
{
    \phpstan('src/Akeneo/Platform/Job/back/tests/phpstan.neon.dist');
    \csFixer('src/Akeneo/Platform/Job/back/tests/.php_cs.php');
    \rector('src/Akeneo/Platform/Job/back/tests/rector.php');
}

#[AsTask(namespace: 'job', name: 'lint-fix-back', description: 'Auto-fix job code style')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/Platform/Job/back/tests/.php_cs.php', dryRun: false);
    \rector('src/Akeneo/Platform/Job/back/tests/rector.php', dryRun: false);
}

#[AsTask(namespace: 'job', name: 'coupling-back', description: 'Run coupling detector for job')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Platform/Job/back/tests/.php_cd.php',
        'src/Akeneo/Platform/Job/back',
    );
}

#[AsTask(namespace: 'job', name: 'unit-back', description: 'Run PHPSpec for job')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run src/Akeneo/Platform/Job/back/tests/Specification');
}

#[AsTask(namespace: 'job', name: 'integration-back', description: 'Run integration tests for job')]
function integrationBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh src/Akeneo/Platform/Job/back/tests .github/scripts/find_phpunit.php Job_Integration_Test');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --testsuite Job_Integration_Test ' . $options);
    }
}

#[AsTask(namespace: 'job', name: 'acceptance-back', description: 'Run acceptance tests for job')]
function acceptanceBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    \appEnvRun('test_fake', 'vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --log-junit var/tests/phpunit/phpunit_job.xml --testsuite Job_Acceptance_Test ' . $options);
}

#[AsTask(namespace: 'job', name: 'ci-back', description: 'Run all job back-end CI checks')]
function ciBack(): void
{
    lintBack();
    couplingBack();
    unitBack();
    acceptanceBack();
    integrationBack();
}

#[AsTask(namespace: 'job', name: 'ci-front', description: 'Run all job front-end CI checks')]
function ciFront(): void
{
    \yarnRun('workspace @akeneo-pim-community/process-tracker lint:check');
    \yarnRun('workspace @akeneo-pim-community/process-tracker test:unit:run');
}

#[AsTask(namespace: 'job', name: 'ci', description: 'Run all job CI checks')]
function ci(): void
{
    ciBack();
    ciFront();
}
