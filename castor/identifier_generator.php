<?php

/**
 * Identifier Generator bounded context tasks.
 * Replaces make-file/identifier-generator.mk.
 */

namespace identifier_generator;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

const PATH = 'components/identifier-generator';

#[AsTask(namespace: 'identifier-generator', name: 'front-check', description: 'Lint + test front-end for identifier generator')]
function frontCheck(): void
{
    \yarnRun('workspace @akeneo-pim-community/identifier-generator lint:check');
    \yarnRun('workspace @akeneo-pim-community/identifier-generator test:unit:run');
}

#[AsTask(namespace: 'identifier-generator', name: 'front-fix', description: 'Fix front-end code style for identifier generator')]
function frontFix(): void
{
    \yarnRun('workspace @akeneo-pim-community/identifier-generator lint:fix');
}

#[AsTask(namespace: 'identifier-generator', name: 'unit-front', description: 'Run front-end unit tests for identifier generator')]
function unitFront(
    #[AsArgument(description: 'Extra Jest options')]
    string $options = '',
): void {
    \yarnRun('workspace @akeneo-pim-community/identifier-generator test:unit:run --ci --coverage ' . $options);
}

#[AsTask(namespace: 'identifier-generator', name: 'unit-back', description: 'Run PHPSpec for identifier generator')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run ' . PATH . '/back/tests/Specification');
}

#[AsTask(namespace: 'identifier-generator', name: 'fix-lint-back', description: 'Fix code style for identifier generator')]
function fixLintBack(): void
{
    \phpRun('vendor/bin/php-cs-fixer fix --config=' . PATH . '/back/tests/.php_cs.php --allow-risky=yes');
}

#[AsTask(namespace: 'identifier-generator', name: 'lint-back', description: 'Run cs-fixer + PHPStan for identifier generator')]
function lintBack(): void
{
    \phpRun('vendor/bin/php-cs-fixer fix --config=' . PATH . '/back/tests/.php_cs.php --allow-risky=yes --dry-run --format=checkstyle | { command -v cs2pr >/dev/null && cs2pr || cat; }');
    \phpstanLevel(PATH . '/back/tests/phpstan.neon', 'github', 'max', PATH . '/back/src/Infrastructure');
    \phpstanLevel(PATH . '/back/tests/phpstan.neon', 'github', 'max', PATH . '/back/src/Domain ' . PATH . '/back/src/Application');
    \phpstanLevel(PATH . '/back/tests/phpstan.neon', 'github', '0', PATH . '/back/tests');
}

#[AsTask(namespace: 'identifier-generator', name: 'acceptance-back', description: 'Run behat acceptance tests for identifier generator')]
function acceptanceBack(
    #[AsArgument(description: 'Extra behat options')]
    string $options = '',
): void {
    \phpRun('vendor/bin/behat --config ' . PATH . '/back/tests/behat.yml --suite=acceptance --format pim --out var/tests/behat/identifier-generator --format progress --out std --colors ' . $options);
}

#[AsTask(namespace: 'identifier-generator', name: 'coupling-back', description: 'Run coupling detector for identifier generator')]
function couplingBack(): void
{
    \phpRun('vendor/bin/php-coupling-detector detect --config-file=' . PATH . '/back/tests/.php_cd.php');
}

#[AsTask(namespace: 'identifier-generator', name: 'phpunit-back', description: 'Run PHPUnit tests for identifier generator')]
function phpunitBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit --testsuite Identifier_Generator_PhpUnit --order-by random --log-junit var/tests/phpunit/phpunit_identifier_generator_end_to_end.xml ' . $options);
}
