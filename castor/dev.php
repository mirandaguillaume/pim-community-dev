<?php

/**
 * @deprecated These targets are deprecated. Use the bounded-context-specific targets instead.
 * Replaces make-file/dev.mk.
 */

namespace dev;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'dev', name: 'phpspec', description: '@deprecated Use bounded-context-unit-back instead')]
function phpspec(
    #[AsArgument(description: 'Spec file or filter')]
    string $options = '',
): void {
    \phpRun('vendor/bin/phpspec run ' . $options);
}

#[AsTask(namespace: 'dev', name: 'acceptance', description: '@deprecated Use bounded-context-acceptance-back instead')]
function acceptance(
    #[AsArgument(description: 'Behat options')]
    string $options = '',
): void {
    \phpRun('vendor/bin/behat -p acceptance ' . $options);
}

#[AsTask(namespace: 'dev', name: 'phpunit', description: '@deprecated Use bounded-context-integration-back instead')]
function phpunit(
    #[AsArgument(description: 'PHPUnit options')]
    string $options = '',
): void {
    \appEnvRun('test', 'vendor/bin/phpunit -c phpunit.xml.dist ' . $options);
}

#[AsTask(namespace: 'dev', name: 'behat-legacy', description: '@deprecated Use test:end-to-end-legacy instead')]
function behatLegacy(
    #[AsArgument(description: 'Behat options')]
    string $options = '',
): void {
    \appEnvRun('behat', 'vendor/bin/behat -p legacy -s all ' . $options);
}

#[AsTask(namespace: 'dev', name: 'xdebug-on', description: 'Start services with Xdebug enabled')]
function xdebugOn(): void
{
    run('XDEBUG_MODE=debug APP_ENV=dev docker-compose up -d --remove-orphans');
}

#[AsTask(namespace: 'dev', name: 'cypress-interactive', description: 'Launch Cypress interactively')]
function cypressInteractive(): void
{
    run('docker-compose -f docker-compose-cypress.yml run --rm -u 1000:1000 -e DISPLAY -v /tmp/.X11-unix:/tmp/.X11-unix --entrypoint cypress cypress open --project .');
}

#[AsTask(namespace: 'dev', name: 'lint-fix-back', description: 'Run php-cs-fixer with Symfony code style')]
function lintFixBack(
    #[AsArgument(description: 'File or directory path')]
    string $options = '',
): void {
    \phpRun('tools/php-cs-fixer fix --config=.php_cs_symfony.dist --diff --path-mode=intersection ' . $options);
}
