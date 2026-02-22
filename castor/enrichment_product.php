<?php

/**
 * Enrichment Product bounded context tasks.
 * Replaces make-file/enrichment-product.mk.
 */

namespace enrichment_product;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

#[AsTask(namespace: 'enrichment-product', name: 'coupling-back', description: 'Run coupling detector for enrichment product')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php', 'src/Akeneo/Pim/Enrichment/Product/back');
}

#[AsTask(namespace: 'enrichment-product', name: 'static-back', description: 'Run PHPStan for enrichment product')]
function staticBack(): void
{
    \phpstan('src/Akeneo/Pim/Enrichment/Product/back/Test/phpstan.neon');
}

#[AsTask(namespace: 'enrichment-product', name: 'unit-back', description: 'Run PHPSpec for enrichment product')]
function unitBack(
    #[AsArgument(description: 'Extra phpspec options')]
    string $options = '',
): void {
    \phpRun('vendor/bin/phpspec run --config=src/Akeneo/Pim/Enrichment/Product/back/Test/phpspec.yml ' . $options);
}

#[AsTask(namespace: 'enrichment-product', name: 'lint-back', description: 'Run cs-fixer for enrichment product')]
function lintBack(): void
{
    \phpRun(
        'vendor/bin/php-cs-fixer fix --dry-run --format=checkstyle --config=.php_cs.php'
        . ' src/Akeneo/Pim/Enrichment/Product/back/API'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Application'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Domain'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Infrastructure'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Test/Acceptance/Context'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Test/Acceptance/InMemory'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Test/Helper'
        . ' src/Akeneo/Pim/Enrichment/Product/back/Test/Integration'
        . ' | { command -v cs2pr >/dev/null && cs2pr || cat; }'
    );
}

#[AsTask(namespace: 'enrichment-product', name: 'integration-back', description: 'Run integration tests for enrichment product')]
function integrationBack(
    #[AsArgument(description: 'Extra PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Enrichment_Product ' . $options);
}

#[AsTask(namespace: 'enrichment-product', name: 'acceptance-back', description: 'Run behat acceptance tests for enrichment product')]
function acceptanceBack(
    #[AsArgument(description: 'Extra behat options')]
    string $options = '',
): void {
    \ensureDir('var/tests/behat/enrichment-product');
    \phpRun('vendor/bin/behat --config src/Akeneo/Pim/Enrichment/Product/back/Test/behat.yml --suite=acceptance --format pim --out var/tests/behat/enrichment-product --format progress --out std --colors ' . $options);
}
