<?php

/**
 * Connectivity Connection bounded context tasks.
 * Replaces make-file/connectivity-connection.mk.
 */

namespace connectivity;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

const YARN_RUN = 'docker-compose run -u node --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome node yarn run';
const FRONT_CWD = '--cwd=src/Akeneo/Connectivity/Connection/front/';
const PERM_CWD = '--cwd=src/Akeneo/Connectivity/Connection/workspaces/permission-form/';

#[AsTask(namespace: 'connectivity-connection', name: 'coupling-back', description: 'Run coupling detector for connectivity')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php',
        'src/Akeneo/Connectivity/Connection/back'
    );
}

#[AsTask(namespace: 'connectivity-connection', name: 'lint-back', description: 'Run cs-fixer + PHPStan + container lint for connectivity')]
function lintBack(): void
{
    if (!\isCI()) {
        \dockerCompose('run --rm php rm -rf var/cache/dev');
        run('APP_ENV=dev docker-compose run -e APP_DEBUG=1 --rm php bin/console cache:warmup');
    }
    \csFixer('src/Akeneo/Connectivity/Connection/back/tests/.php_cs.php');
    \phpstan('src/Akeneo/Connectivity/Connection/back/tests/phpstan.neon');
    \phpstanLevel(
        'src/Akeneo/Connectivity/Connection/back/tests/phpstan-deprecations.neon',
        'github',
        '1',
        'src/Akeneo/Connectivity/Connection/back'
    );
    \phpRun('bin/console lint:container');
}

#[AsTask(namespace: 'connectivity-connection', name: 'lint-back-fix', description: 'Fix code style for connectivity')]
function lintBackFix(): void
{
    \csFixer('src/Akeneo/Connectivity/Connection/back/tests/.php_cs.php', dryRun: false);
}

#[AsTask(namespace: 'connectivity-connection', name: 'unit-back', description: 'Run PHPSpec for connectivity')]
function unitBack(): void
{
    if (\isCI()) {
        run('docker-compose run -T --rm php php vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml');
        run('.github/scripts/find_non_executed_phpspec.sh');
    }
    \phpRun('vendor/bin/phpspec run src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/');
    // Scope Mapper unit tests
    \phpRun('vendor/bin/phpspec run tests/back/Pim/Structure/Specification/Component/Security/');
    \phpRun('vendor/bin/phpspec run tests/back/Pim/Enrichment/Specification/Component/Security/');
    \phpRun('vendor/bin/phpspec run tests/back/Channel/Specification/Infrastructure/Component/Security/');
}

#[AsTask(namespace: 'connectivity-connection', name: 'critical-e2e', description: 'Run critical behat e2e tests for connectivity')]
function criticalE2e(): void
{
    \ensureDir('var/tests/behat/connectivity/connection');
    \appEnvRun('behat', 'vendor/bin/behat --config behat.yml -p legacy -s connectivity src/Akeneo/Connectivity/Connection/tests/features/activate_an_app.feature');
    \appEnvRun('behat', 'vendor/bin/behat --config behat.yml -p legacy -s connectivity src/Akeneo/Connectivity/Connection/tests/features/edit_connection.feature');
}

#[AsTask(namespace: 'connectivity-connection', name: 'integration-back', description: 'Run integration tests for connectivity')]
function integrationBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php Akeneo_Connectivity_Connection_Integration');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_Integration --log-junit var/tests/phpunit/phpunit_connectivity_integration.xml ' . $options);
    }
}

#[AsTask(namespace: 'connectivity-connection', name: 'e2e-back', description: 'Run end-to-end tests for connectivity')]
function e2eBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php Akeneo_Connectivity_Connection_EndToEnd');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_EndToEnd --log-junit var/tests/phpunit/phpunit_connectivity_e2e.xml ' . $options);
    }
}

#[AsTask(namespace: 'connectivity-connection', name: 'back', description: 'Run all backend checks for connectivity')]
function back(): void
{
    couplingBack();
    lintBack();
    unitBack();
    integrationBack();
    e2eBack();
}

#[AsTask(namespace: 'connectivity-connection', name: 'unit-front', description: 'Run front-end unit tests for connectivity')]
function unitFront(
    #[AsArgument(description: 'Extra Jest options')]
    string $options = '',
): void {
    run(YARN_RUN . ' ' . FRONT_CWD . ' jest --ci ' . $options);
    run(YARN_RUN . ' ' . PERM_CWD . ' jest --ci --coverage ' . $options);
}

#[AsTask(namespace: 'connectivity-connection', name: 'lint-front', description: 'Run front-end linting for connectivity')]
function lintFront(): void
{
    run(YARN_RUN . ' ' . FRONT_CWD . ' eslint');
    run(YARN_RUN . ' ' . FRONT_CWD . ' prettier --check');
    run(YARN_RUN . ' ' . FRONT_CWD . ' tsc --noEmit --strict');
    run(YARN_RUN . ' ' . PERM_CWD . ' eslint');
    run(YARN_RUN . ' ' . PERM_CWD . ' prettier --check');
    run(YARN_RUN . ' ' . PERM_CWD . ' tsc --noEmit --strict');
}

#[AsTask(namespace: 'connectivity-connection', name: 'unit-front-coverage', description: 'Run front-end unit tests with coverage')]
function unitFrontCoverage(): void
{
    run(YARN_RUN . ' ' . FRONT_CWD . ' jest --coverage');
}

#[AsTask(namespace: 'connectivity-connection', name: 'unit-front-watch', description: 'Run front-end unit tests in watch mode')]
function unitFrontWatch(): void
{
    run(YARN_RUN . ' ' . FRONT_CWD . ' jest --watchAll');
}

#[AsTask(namespace: 'connectivity-connection', name: 'lint-front-fix', description: 'Fix front-end code style for connectivity')]
function lintFrontFix(): void
{
    run(YARN_RUN . ' ' . FRONT_CWD . ' eslint --fix');
    run(YARN_RUN . ' ' . FRONT_CWD . ' prettier --write');
    run(YARN_RUN . ' ' . PERM_CWD . ' eslint --fix');
    run(YARN_RUN . ' ' . PERM_CWD . ' prettier --write');
}

#[AsTask(namespace: 'connectivity-connection', name: 'coverage', description: 'Generate code coverage report for connectivity')]
function coverage(
    #[AsArgument(description: 'Extra options')]
    string $options = '',
): void {
    // Backend unit tests with coverage
    run('XDEBUG_MODE=coverage docker-compose run --rm php php vendor/bin/phpspec run'
        . ' -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist'
        . ' src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/');

    // Backend integration tests with coverage
    run('XDEBUG_MODE=coverage APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit'
        . ' -c src/Akeneo/Connectivity/Connection/back/tests/'
        . ' --coverage-clover coverage/Connectivity/Back/Integration/coverage.cov'
        . ' --coverage-php coverage/Connectivity/Back/Integration/coverage.php'
        . ' --coverage-html coverage/Connectivity/Back/Integration/'
        . ' --testsuite Integration ' . $options);

    // Backend e2e tests with coverage
    run('XDEBUG_MODE=coverage APP_ENV=test docker-compose run --rm php php vendor/bin/phpunit'
        . ' -c src/Akeneo/Connectivity/Connection/back/tests/'
        . ' --coverage-clover coverage/Connectivity/Back/EndToEnd/coverage.cov'
        . ' --coverage-php coverage/Connectivity/Back/EndToEnd/coverage.php'
        . ' --coverage-html coverage/Connectivity/Back/EndToEnd/'
        . ' --testsuite EndToEnd ' . $options);

    \dockerCompose('run --rm php mkdir -p var/tests/behat/connectivity/connection');

    // Download phpcov binary
    \dockerCompose('run --rm php sh -c "test -e phpcov.phar || wget https://phar.phpunit.de/phpcov.phar && php phpcov.phar --version"');

    // Merge coverage
    \dockerCompose('run --rm php sh -c "'
        . 'if [ -d coverage/Connectivity/Back/Global/ ]; then rm -r coverage/Connectivity/Back/Global/; fi && '
        . 'mkdir -p coverage/Connectivity/Back/Global/ && '
        . 'cp coverage/Connectivity/Back/Unit/coverage.php coverage/Connectivity/Back/Global/Unit.cov && '
        . 'cp coverage/Connectivity/Back/Integration/coverage.php coverage/Connectivity/Back/Global/Integration.cov && '
        . 'cp coverage/Connectivity/Back/EndToEnd/coverage.php coverage/Connectivity/Back/Global/EndToEnd.cov"');

    run('XDEBUG_MODE=coverage docker-compose run --rm php php -d memory_limit=-1 phpcov.phar merge'
        . ' --clover coverage/Connectivity/Back/Global/coverage.cov'
        . ' --html coverage/Connectivity/Back/Global/'
        . ' coverage/Connectivity/Back/Global/');
}

#[AsTask(namespace: 'connectivity-connection', name: 'unused-coupling-rules', description: 'List unused coupling rules for connectivity')]
function unusedCouplingRules(): void
{
    \phpRun('vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php src/Akeneo/Connectivity/Connection/back');
}
