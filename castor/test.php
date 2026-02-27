<?php

/**
 * Main CI test tasks.
 * Replaces make-file/test.mk: lint-back, unit-back, acceptance-back, coupling-back, etc.
 */

namespace test;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'test', name: 'find-legacy-translations', description: 'Find legacy translation usage')]
function findLegacyTranslations(): void
{
    run('.github/scripts/find_legacy_translations.sh');
}

#[AsTask(namespace: 'test', name: 'coupling-back', description: 'Run all coupling detectors')]
function couplingBack(): void
{
    \structure\couplingBack();
    \user_management\couplingBack();
    \channel\couplingBack();
    \enrichment\couplingBack();
    \connectivity\couplingBack();
    \communication_channel\couplingBack();
    \import_export\couplingBack();
    \job\couplingBack();
    \data_quality_insights\couplingBack();
    \enrichment_product\couplingBack();
    migrationCouplingBack();
    \identifier_generator\couplingBack();
    \installer\couplingBack();
}

#[AsTask(namespace: 'test', name: 'migration-coupling-back', description: 'Run coupling detector for migrations')]
function migrationCouplingBack(): void
{
    \couplingDetector('upgrades/.php_cd.php', 'upgrades/schema');
}

#[AsTask(namespace: 'test', name: 'static-back', description: 'Run static checks (pullup, services, enrichment-product)')]
function staticBack(): void
{
    checkPullup();
    checkSfServices();
    \enrichment_product\staticBack();
    echo "Job done! Nothing more to do here...\n";
}

#[AsTask(namespace: 'test', name: 'check-pullup', description: 'Check pullup configuration')]
function checkPullup(): void
{
    \phpRun('bin/check-pullup');
}

#[AsTask(namespace: 'test', name: 'check-sf-services', description: 'Lint Symfony service container')]
function checkSfServices(): void
{
    \phpRun('bin/console lint:container');
}

#[AsTask(namespace: 'test', name: 'lint-back', description: 'Run all back-end linting (PHPStan + cs-fixer + per-context)')]
function lintBack(): void
{
    \dockerCompose('run --rm php rm -rf var/cache/dev');
    run('APP_ENV=dev docker-compose run -e APP_DEBUG=1 --rm php bin/console cache:warmup');
    \phpRun('-d memory_limit=1G vendor/bin/phpstan analyse src/Akeneo/Pim --level 2 --error-format=github');
    \csFixer('.php_cs.php', dryRun: true);
    \category\lintBack();
    \channel\lintBack();
    \communication_channel\lintBack();
    \connectivity\lintBack();
    \data_quality_insights\lintBack();
    \data_quality_insights\phpstanTask();
    \enrichment_product\lintBack();
    \identifier_generator\lintBack();
    \import_export\lintBack();
    \installer\lintBack();
    \job\lintBack();
    \measurement\lintBack();
    migrationLintBack();
    // Cache was created with debug enabled, removing it allows a faster one to be created for upcoming tests
    \dockerCompose('run --rm php rm -rf var/cache/dev');
}

#[AsTask(namespace: 'test', name: 'deprecation-back', description: 'Run PHPStan deprecation analysis')]
function deprecationBack(): void
{
    run('APP_ENV=dev docker-compose run -e APP_DEBUG=1 --rm php bin/console cache:warmup');
    \phpRun('-d memory_limit=2G vendor/bin/phpstan analyse -c phpstan-deprecations.neon --level 1 --error-format=github');
    \dockerCompose('run --rm php rm -rf var/cache/dev');
}

#[AsTask(namespace: 'test', name: 'migration-lint-back', description: 'Run PHPStan for migrations')]
function migrationLintBack(): void
{
    \phpstan('upgrades/phpstan.neon');
}

#[AsTask(namespace: 'test', name: 'lint-front', description: 'Run front-end linting')]
function lintFront(): void
{
    \yarnRun('lint');
    \connectivity\lintFront();
}

#[AsTask(namespace: 'test', name: 'unit-back', description: 'Run all PHPSpec unit tests')]
function unitBack(): void
{
    \ensureDir('var/tests/phpspec');

    if (\isCI()) {
        run('docker-compose run -T --rm php php vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml');
        run('.github/scripts/find_non_executed_phpspec.sh');
    } else {
        \phpRun('vendor/bin/phpspec run');
    }
}

#[AsTask(namespace: 'test', name: 'unit-front', description: 'Run all front-end unit tests')]
function unitFront(): void
{
    \yarnRun('unit');
    \connectivity\unitFront();
}

#[AsTask(namespace: 'test', name: 'acceptance-back', description: 'Run all back-end acceptance tests')]
function acceptanceBack(): void
{
    \appEnvRun('behat', 'vendor/bin/behat -p acceptance --format pim --out var/tests/behat --format progress --out std --colors');
    \import_export\acceptanceBack();
    \job\acceptanceBack();
    \channel\acceptanceBack();
    \measurement\acceptanceBack();
    \identifier_generator\acceptanceBack();
    \installer\acceptanceBack();
}

#[AsTask(namespace: 'test', name: 'acceptance-back-contexts', description: 'Run bounded context acceptance tests')]
function acceptanceBackContexts(): void
{
    // Ensure Docker services (MySQL, etc.) are started before running tests
    \dockerCompose('up -d mysql elasticsearch');

    \category\acceptanceBack();
    \import_export\acceptanceBack();
    \job\acceptanceBack();
    \channel\acceptanceBack();
    \measurement\acceptanceBack();
    \identifier_generator\acceptanceBack();
    \installer\acceptanceBack();
    \enrichment_product\acceptanceBack();
}

#[AsTask(namespace: 'test', name: 'acceptance-front', description: 'Run front-end acceptance tests')]
function acceptanceFront(): void
{
    run('MAX_RANDOM_LATENCY_MS=100 docker-compose run -u node --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome node yarn acceptance run acceptance ./tests/features');
}

#[AsTask(namespace: 'test', name: 'integration-front', description: 'Run front-end integration tests')]
function integrationFront(): void
{
    \yarnRun('integration');
}

#[AsTask(namespace: 'test', name: 'pim-integration-back', description: 'Run PIM back-end integration tests')]
function pimIntegrationBack(): void
{
    \ensureDir('var/tests/phpunit');

    // Run context-specific integration tests first
    \connectivity\integrationBack();
    \communication_channel\integrationBack();
    \job\integrationBack();
    \channel\integrationBack();
    \identifier_generator\phpunitBack();
    \installer\integrationBack();

    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php PIM_Integration_Test');
    } else {
        echo "Run integration test locally is too long, please use the target defined for your bounded context (ex: bounded-context-integration-back)\n";
    }
}

#[AsTask(namespace: 'test', name: 'migration-back', description: 'Run database migration tests')]
function migrationBack(): void
{
    \ensureDir('var/tests/phpunit');

    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php PIM_Migration_Test');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c . --testsuite PIM_Migration_Test');
    }
}

#[AsTask(namespace: 'test', name: 'end-to-end-back', description: 'Run back-end end-to-end tests')]
function endToEndBack(): void
{
    \ensureDir('var/tests/phpunit');

    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php End_to_End');
    } else {
        echo "Run end to end test locally is too long, please use the target defined for your bounded context (ex: bounded-context-end-to-end-back)\n";
    }
}

#[AsTask(namespace: 'test', name: 'end-to-end-front', description: 'Run front-end end-to-end tests (Playwright)')]
function endToEndFront(): void
{
    run('npx playwright test');
}

#[AsTask(namespace: 'test', name: 'end-to-end-legacy', description: 'Run legacy behat end-to-end tests')]
function endToEndLegacy(
    #[AsArgument(description: 'Feature file path (e.g. path/to/feature.feature:23)')]
    string $options = '',
): void {
    \ensureDir('var/tests/behat');

    if (\isCI()) {
        $suite = getenv('SUITE') ?: '';
        run('.github/scripts/run_behat.sh ' . $suite);
        run('.github/scripts/run_behat.sh critical');
    } else {
        \appEnvRun('behat', 'vendor/bin/behat -p legacy -s all ' . $options);
    }
}
