<?php

/**
 * Import/Export bounded context tasks.
 * Replaces make-file/import-export.mk.
 */

namespace import_export;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'import-export', name: 'lint-back', description: 'Run PHPStan and cs-fixer for import/export')]
function lintBack(): void
{
    \phpRun('-d memory_limit=1G vendor/bin/phpstan analyse src/Akeneo/Platform/Bundle/ImportExportBundle --level 5 --error-format=github');
    \csFixer('src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cs.php');
}

#[AsTask(namespace: 'import-export', name: 'lint-fix-back', description: 'Auto-fix import/export code style')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cs.php', dryRun: false);
}

#[AsTask(namespace: 'import-export', name: 'coupling-back', description: 'Run coupling detector for import/export')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cd.php',
        'src/Akeneo/Platform/Bundle/ImportExportBundle',
    );
}

#[AsTask(namespace: 'import-export', name: 'unit-back', description: 'Run PHPSpec for import/export')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run tests/back/Platform/Specification/Bundle/ImportExportBundle');
}

#[AsTask(namespace: 'import-export', name: 'integration-back', description: 'Run integration tests for import/export')]
function integrationBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh src/Akeneo/Platform/Bundle/ImportExportBundle/Test .github/scripts/find_phpunit.php ImportExport_Integration_Test');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/ImportExportBundle/Test --testsuite ImportExport_Integration_Test ' . $options);
    }
}

#[AsTask(namespace: 'import-export', name: 'acceptance-back', description: 'Run acceptance tests for import/export')]
function acceptanceBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    \appEnvRun('test_fake', 'vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/ImportExportBundle/Test --log-junit var/tests/phpunit/phpunit_import_export.xml --testsuite ImportExport_Acceptance_Test ' . $options);
}

#[AsTask(namespace: 'import-export', name: 'ci-back', description: 'Run all import/export back-end CI checks')]
function ciBack(): void
{
    lintBack();
    couplingBack();
    unitBack();
    acceptanceBack();
    integrationBack();
}

#[AsTask(namespace: 'import-export', name: 'ci-front', description: 'Run all import/export front-end CI checks')]
function ciFront(): void
{
    \yarnRun('workspace @akeneo-pim-community/import-export lint:check');
    \yarnRun('workspace @akeneo-pim-community/import-export test:unit:run');
}

#[AsTask(namespace: 'import-export', name: 'ci', description: 'Run all import/export CI checks')]
function ci(): void
{
    ciBack();
    ciFront();
}
