<?php

/**
 * Shared helper functions for Castor tasks.
 * Replaces Makefile variables: DOCKER_COMPOSE, PHP_RUN, PHP_EXEC, YARN_RUN, NODE_RUN.
 */

use function Castor\context;
use function Castor\run;

function dockerCompose(string $args): void
{
    run('docker-compose ' . $args);
}

function phpRun(string $cmd, ?float $timeout = null): void
{
    $ctx = $timeout !== null ? context()->withTimeout($timeout) : null;
    run('docker-compose run --rm php php ' . $cmd, context: $ctx);
}

function phpExec(string $cmd): void
{
    run('docker-compose exec -u www-data httpd php ' . $cmd);
}

function yarnRun(string $cmd): void
{
    run(
        'docker-compose run -u node --rm'
        . ' -e YARN_REGISTRY'
        . ' -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1'
        . ' -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome'
        . ' node yarn ' . $cmd,
    );
}

function nodeRun(string $cmd): void
{
    run(
        'docker-compose run -u node --rm'
        . ' -e YARN_REGISTRY'
        . ' -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1'
        . ' -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome'
        . ' node ' . $cmd,
    );
}

function isCI(): bool
{
    return getenv('CI') === 'true';
}

function appEnvRun(string $env, string $cmd): void
{
    run('APP_ENV=' . $env . ' docker-compose run --rm php php ' . $cmd);
}

function ensureDir(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function csFixer(string $config, bool $dryRun = true, string $path = ''): void
{
    $cmd = 'vendor/bin/php-cs-fixer fix'
        . ($dryRun ? ' --dry-run --format=checkstyle' : ' --diff')
        . ' --config=' . $config;

    if ('' !== $path) {
        $cmd .= ' ' . $path;
    }

    if ($dryRun) {
        $cmd .= ' | { command -v cs2pr >/dev/null && cs2pr || cat; }';
    }

    phpRun($cmd);
}

function phpstan(string $config, string $errorFormat = 'github'): void
{
    phpRun('vendor/bin/phpstan analyse --configuration ' . $config . ' --error-format=' . $errorFormat);
}

function phpstanLevel(string $config, string $errorFormat, string $level, string $paths): void
{
    phpRun('vendor/bin/phpstan analyse --level=' . $level . ' --configuration ' . $config . ' --error-format=' . $errorFormat . ' ' . $paths);
}

function couplingDetector(string $configFile, string $path): void
{
    phpRun('vendor/bin/php-coupling-detector detect --config-file=' . $configFile . ' ' . $path);
    phpRun('vendor/bin/php-coupling-detector list-unused-requirements --config-file=' . $configFile . ' ' . $path);
}

function rector(string $config, bool $dryRun = true): void
{
    $cmd = 'vendor/bin/rector process' . ($dryRun ? ' --dry-run' : '') . ' --config=' . $config;
    phpRun($cmd);
}
