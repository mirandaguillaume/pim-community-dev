<?php

/**
 * Installer bounded context tasks.
 * Replaces make-file/installer.mk.
 */

namespace installer;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'installer', name: 'lint-back', description: 'Run PHPStan, cs-fixer and Rector for installer')]
function lintBack(): void
{
    \phpstan('src/Akeneo/Platform/Installer/back/tests/phpstan.neon');
    \csFixer('src/Akeneo/Platform/Installer/back/tests/.php_cs.src.php');
    \csFixer('src/Akeneo/Platform/Installer/back/tests/.php_cs.tests.php');
    \rector('src/Akeneo/Platform/Installer/back/tests/rector.php');
}

#[AsTask(namespace: 'installer', name: 'lint-fix-back', description: 'Auto-fix installer code style')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/Platform/Installer/back/tests/.php_cs.src.php', dryRun: false);
    \csFixer('src/Akeneo/Platform/Installer/back/tests/.php_cs.tests.php', dryRun: false);
    \rector('src/Akeneo/Platform/Installer/back/tests/rector.php', dryRun: false);
}

#[AsTask(namespace: 'installer', name: 'coupling-back', description: 'Run coupling detector for installer')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Platform/Installer/back/tests/.php_cd.php',
        'src/Akeneo/Platform/Installer/back/src',
    );
}

#[AsTask(namespace: 'installer', name: 'unit-back', description: 'Run PHPSpec for installer')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run -vvv src/Akeneo/Platform/Installer/back/tests/Specification');
}

#[AsTask(namespace: 'installer', name: 'integration-back', description: 'Run integration tests for installer')]
function integrationBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh src/Akeneo/Platform/Installer/back/tests/phpunit.xml .github/scripts/find_phpunit.php Installer_Integration_Test');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Platform/Installer/back/tests/phpunit.xml --testsuite Installer_Integration_Test ' . $options);
    }
}

#[AsTask(namespace: 'installer', name: 'acceptance-back', description: 'Run acceptance tests for installer')]
function acceptanceBack(): void
{
    \appEnvRun('test_fake', 'vendor/bin/phpunit -c src/Akeneo/Platform/Installer/back/tests/phpunit.xml --log-junit var/tests/phpunit/phpunit_installer.xml --testsuite Installer_Acceptance_Test');
}

#[AsTask(namespace: 'installer', name: 'ci', description: 'Run all installer CI checks')]
function ci(): void
{
    lintBack();
    couplingBack();
    unitBack();
    acceptanceBack();
    integrationBack();
}
