<?php

/**
 * Category bounded context tasks.
 * Replaces make-file/category.mk.
 */

namespace category;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

#[AsTask(namespace: 'category', name: 'front-up', description: 'Launch category micro front-end (http://localhost:3000/)')]
function frontUp(): void
{
    \dockerCompose('-f docker-compose.yml -f src/Akeneo/Category/front/docker-compose.micro-frontend.yml up -d --remove-orphans');
}

#[AsTask(namespace: 'category', name: 'front-down', description: 'Stop category micro front-end')]
function frontDown(): void
{
    \dockerCompose('-f docker-compose.yml down');
}

#[AsTask(namespace: 'category', name: 'lint-back', description: 'Run PHPStan + cs-fixer for category')]
function lintBack(): void
{
    \phpstan('src/Akeneo/Category/back/tests/phpstan.neon.dist');
    \csFixer('src/Akeneo/Category/back/tests/.php_cs.php');
}

#[AsTask(namespace: 'category', name: 'lint-front', description: 'Run front-end linting for category')]
function lintFront(): void
{
    \yarnRun('workspace @akeneo-pim-community/category lint:check');
}

#[AsTask(namespace: 'category', name: 'lint-fix-back', description: 'Fix code style for category')]
function lintFixBack(): void
{
    \csFixer('src/Akeneo/Category/back/tests/.php_cs.php', dryRun: false);
}

#[AsTask(namespace: 'category', name: 'lint-fix-front', description: 'Fix front-end code style for category')]
function lintFixFront(): void
{
    \yarnRun('workspace @akeneo-pim-community/category lint:fix');
}

#[AsTask(namespace: 'category', name: 'coupling-back', description: 'Run coupling detector for category')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/Category/back/tests/.php_cd.php', 'src/Akeneo/Category/back');
}

#[AsTask(namespace: 'category', name: 'unit-back', description: 'Run PHPSpec + PHPUnit unit tests for category')]
function unitBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \phpRun('vendor/bin/phpspec run src/Akeneo/Category/back/tests/Specification');
    \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_Unit_Test ' . $options);
}

#[AsTask(namespace: 'category', name: 'unit-front', description: 'Run front-end unit tests for category')]
function unitFront(): void
{
    \yarnRun('workspace @akeneo-pim-community/category test:unit:run');
}

#[AsTask(namespace: 'category', name: 'integration-back', description: 'Run integration tests for category')]
function integrationBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_Integration_Test ' . $options);
}

#[AsTask(namespace: 'category', name: 'end-to-end-back', description: 'Run end-to-end tests for category')]
function endToEndBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_EndToEnd_Test ' . $options);
}

#[AsTask(namespace: 'category', name: 'acceptance-back', description: 'Run behat acceptance tests for category')]
function acceptanceBack(): void
{
    \ensureDir('var/tests/behat/enrichment-category');
    $features = [
        'tests/legacy/features/pim/enrichment/category/create_a_category.feature',
        'tests/legacy/features/pim/enrichment/category/edit_a_category.feature',
        'tests/legacy/features/pim/enrichment/category/export_categories_csv.feature',
        'tests/legacy/features/pim/enrichment/category/export_categories_xlsx.feature',
        'tests/legacy/features/pim/enrichment/category/import_categories.feature',
        'tests/legacy/features/pim/enrichment/category/list_categories.feature',
        'tests/legacy/features/pim/enrichment/category/remove_a_category.feature',
    ];
    foreach ($features as $feature) {
        \appEnvRun('behat', 'vendor/bin/behat --config behat.yml -p legacy ' . $feature);
    }
}

#[AsTask(namespace: 'category', name: 'ci-back', description: 'Run all CI checks for category backend')]
function ciBack(): void
{
    lintBack();
    couplingBack();
    unitBack();
    integrationBack();
    endToEndBack();
}

#[AsTask(namespace: 'category', name: 'ci-front', description: 'Run all CI checks for category frontend')]
function ciFront(): void
{
    unitFront();
}

#[AsTask(namespace: 'category', name: 'ci', description: 'Run all CI checks for category')]
function ci(): void
{
    ciBack();
    ciFront();
}
