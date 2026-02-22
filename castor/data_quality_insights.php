<?php

/**
 * Data Quality Insights bounded context tasks.
 * Replaces make-file/data-quality-insights.mk.
 */

namespace data_quality_insights;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

#[AsTask(namespace: 'data-quality-insights', name: 'coupling-back', description: 'Run coupling detector for DQI')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/.php_cd.php',
        'src/Akeneo/Pim/Automation/DataQualityInsights'
    );
}

#[AsTask(namespace: 'data-quality-insights', name: 'phpstan', description: 'Run PHPStan for DQI')]
function phpstanTask(): void
{
    // Requires dev cache to exist
    \phpstan('src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/phpstan.neon.dist');
}

#[AsTask(namespace: 'data-quality-insights', name: 'unit-back', description: 'Run PHPSpec for DQI')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/Specification');
}

#[AsTask(namespace: 'data-quality-insights', name: 'lint-back', description: 'Run cs-fixer for DQI')]
function lintBack(): void
{
    \csFixer('.php_cs.php', dryRun: true, path: 'src/Akeneo/Pim/Automation/DataQualityInsights/back');
}

#[AsTask(namespace: 'data-quality-insights', name: 'cs-fix', description: 'Fix code style for DQI')]
function csFix(): void
{
    \csFixer('.php_cs.php', dryRun: false, path: 'src/Akeneo/Pim/Automation/DataQualityInsights/back');
}

#[AsTask(namespace: 'data-quality-insights', name: 'integration-back', description: 'Run integration tests for DQI')]
function integrationBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit --testsuite=Data_Quality_Insights --testdox ' . $options);
}

#[AsTask(namespace: 'data-quality-insights', name: 'lint-front', description: 'Run front-end linting for DQI')]
function lintFront(): void
{
    \yarnRun('prettier --parser typescript --check "./src/Akeneo/Pim/Automation/DataQualityInsights/front/**/*.{js,ts,tsx}"');
}

#[AsTask(namespace: 'data-quality-insights', name: 'lint-front-fix', description: 'Fix front-end code style for DQI')]
function lintFrontFix(): void
{
    \yarnRun('prettier --parser typescript --write "./src/Akeneo/Pim/Automation/DataQualityInsights/front/**/*.{js,ts,tsx}"');
}

#[AsTask(namespace: 'data-quality-insights', name: 'unit-front', description: 'Run front-end unit tests for DQI')]
function unitFront(
    #[AsArgument(description: 'Extra Jest options')]
    string $options = '',
    bool $watch = false,
): void {
    $watchFlag = $watch ? '--watchAll' : '';
    \yarnRun('jest --coverage=false --maxWorkers=4 --config src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/unit.jest.js ' . $watchFlag . ' ' . $options);
}

#[AsTask(namespace: 'data-quality-insights', name: 'tests', description: 'Run all DQI tests')]
function tests(): void
{
    couplingBack();
    lintBack();
    phpstanTask();
    unitBack();
    unitFront();
    integrationBack();
}
