<?php

/**
 * PIM installation and Docker tasks.
 * Replaces: dependencies, pim-behat, pim-test, pim-dev, pim-prod, up, down, upgrade-front.
 */

namespace install;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'docker', name: 'up', description: 'Start Docker services')]
function up(
    #[AsArgument(description: 'Services to start (e.g. "httpd mysql elasticsearch")')]
    string $services = '',
): void {
    \dockerCompose('up -d --remove-orphans ' . $services);
}

#[AsTask(namespace: 'docker', name: 'down', description: 'Stop Docker services')]
function down(): void
{
    \dockerCompose('down -v');
}

#[AsTask(namespace: 'pim', name: 'dependencies', description: 'Install back and front dependencies')]
function dependencies(): void
{
    \back\vendor();
    \front\nodeModules();
}

#[AsTask(namespace: 'pim', name: 'behat', description: 'Initialize PIM for behat tests')]
function behat(): void
{
    run('APP_ENV=behat docker-compose up -d --remove-orphans');
    run('APP_ENV=behat docker-compose run --rm php php bin/console cache:warmup');
    \front\assets();
    \front\css();
    \front\frontPackages();
    \front\javascriptDev();
    run('docker/wait_docker_up.sh');
    run('APP_ENV=behat docker-compose run --rm php php bin/console pim:installer:db');
    run('APP_ENV=behat docker-compose run --rm php php bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US');
}

#[AsTask(namespace: 'pim', name: 'test', description: 'Initialize PIM for test environment')]
function test(): void
{
    run('APP_ENV=test docker-compose up -d --remove-orphans');
    run('APP_ENV=test docker-compose run --rm php php bin/console cache:warmup');
    run('docker/wait_docker_up.sh');
    run('APP_ENV=test docker-compose run --rm php php bin/console pim:installer:db');
}

#[AsTask(namespace: 'pim', name: 'dev', description: 'Initialize PIM for development')]
function dev(): void
{
    run('APP_ENV=dev docker-compose up -d --remove-orphans');
    run('APP_ENV=dev docker-compose run --rm php php bin/console cache:warmup');
    \front\assets();
    \front\css();
    \front\frontPackages();
    \front\javascriptDev();
    run('docker/wait_docker_up.sh');
    run('APP_ENV=dev docker-compose run --rm php php bin/console pim:installer:db --catalog src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/icecat_demo_dev');
}

#[AsTask(namespace: 'pim', name: 'prod', description: 'Initialize PIM for production')]
function prod(): void
{
    run('APP_ENV=prod docker-compose up -d --remove-orphans');
    run('APP_ENV=prod docker-compose run --rm php php bin/console cache:warmup');
    \front\assets();
    \front\css();
    \front\frontPackages();
    \front\javascriptProd();
    run('docker/wait_docker_up.sh');
    run('APP_ENV=prod docker-compose run --rm php php bin/console pim:installer:db');
}

#[AsTask(namespace: 'pim', name: 'upgrade-front', description: 'Upgrade front-end assets')]
function upgradeFront(): void
{
    \front\nodeModules();
    \back\cache();
    \front\assets();
    \front\frontPackages();
    \front\javascriptProd();
    \front\css();
    \front\javascriptExtensions();
}
