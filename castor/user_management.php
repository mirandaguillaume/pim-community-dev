<?php

/**
 * User Management bounded context tasks.
 * Replaces make-file/user-management.mk.
 */

namespace user_management;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

#[AsTask(namespace: 'user-management', name: 'lint-back', description: 'Run cs-fixer for user management')]
function lintBack(): void
{
    \csFixer('src/Akeneo/UserManagement/back/tests/.php_cs.php');
}

#[AsTask(namespace: 'user-management', name: 'lint-fix-back', description: 'Auto-fix user management code style')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/UserManagement/back/tests/.php_cs.php', dryRun: false);
}

#[AsTask(namespace: 'user-management', name: 'unit-back', description: 'Run PHPSpec for user management')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run tests/back/UserManagement/Specification');
    \phpRun('vendor/bin/phpspec run src/Akeneo/UserManagement/back/tests/Specification');
}

#[AsTask(namespace: 'user-management', name: 'coupling-back', description: 'Run coupling detector for user management')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/UserManagement/.php_cd.php', 'src/Akeneo/UserManagement');
}

#[AsTask(namespace: 'user-management', name: 'integration-back', description: 'Run integration tests for user management')]
function integrationBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit --testsuite PIM_Integration_Test --filter UserManagement ' . $options);
}

#[AsTask(namespace: 'user-management', name: 'end-to-end-back', description: 'Run end-to-end tests for user management')]
function endToEndBack(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit --testsuite End_to_End --filter UserManagement ' . $options);
}

#[AsTask(namespace: 'user-management', name: 'ci', description: 'Run all user management CI checks')]
function ci(): void
{
    lintBack();
    unitBack();
    couplingBack();
    integrationBack();
    endToEndBack();
}
