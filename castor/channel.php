<?php

/**
 * Channel bounded context tasks.
 * Replaces make-file/channel.mk.
 */

namespace channel;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

#[AsTask(namespace: 'channel', name: 'lint-back', description: 'Run PHPStan + cs-fixer for channel')]
function lintBack(): void
{
    \phpstan('src/Akeneo/Channel/back/tests/phpstan.neon.dist');
    \csFixer('src/Akeneo/Channel/back/tests/.php_cs.php');
}

#[AsTask(namespace: 'channel', name: 'lint-fix-back', description: 'Fix code style for channel')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/Channel/back/tests/.php_cs.php', dryRun: false);
}

#[AsTask(namespace: 'channel', name: 'coupling-back', description: 'Run coupling detector for channel')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/Channel/back/tests/.php_cd.php', 'src/Akeneo/Channel/back');
}

#[AsTask(namespace: 'channel', name: 'unit-back', description: 'Run PHPSpec for channel')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run src/Akeneo/Channel/back/tests/Specification');
    \phpRun('vendor/bin/phpspec run src/Akeneo/Channel/back/tests/Acceptance/Specification');
}

#[AsTask(namespace: 'channel', name: 'integration-back', description: 'Run integration tests for channel')]
function integrationBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Integration_Test ' . $options);
}

#[AsTask(namespace: 'channel', name: 'acceptance-back', description: 'Run acceptance tests for channel')]
function acceptanceBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test_fake', 'vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Acceptance_Test ' . $options);
}

#[AsTask(namespace: 'channel', name: 'ci-back', description: 'Run all CI checks for channel backend')]
function ciBack(): void
{
    lintBack();
    couplingBack();
    unitBack();
    acceptanceBack();
    integrationBack();
}

#[AsTask(namespace: 'channel', name: 'ci', description: 'Run all CI checks for channel')]
function ci(): void
{
    ciBack();
}
