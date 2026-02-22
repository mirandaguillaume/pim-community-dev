<?php

/**
 * Front-end tasks.
 * Replaces targets from the root Makefile: node_modules, javascript-*, front-packages, dsm, assets, css.
 */

namespace front;

use Castor\Attribute\AsTask;

#[AsTask(namespace: 'front', name: 'node-modules', description: 'Install front-end dependencies')]
function nodeModules(): void
{
    \yarnRun('install --frozen-lockfile');
}

#[AsTask(namespace: 'front', name: 'javascript-extensions', description: 'Update JavaScript extensions')]
function javascriptExtensions(): void
{
    \yarnRun('run update-extensions');
}

#[AsTask(namespace: 'front', name: 'front-packages', description: 'Build shared front-end packages')]
function frontPackages(): void
{
    \yarnRun('packages:build');
}

#[AsTask(namespace: 'front', name: 'dsm', description: 'Build design system')]
function dsm(): void
{
    \yarnRun('dsm:build');
}

#[AsTask(namespace: 'front', name: 'assets', description: 'Refresh Symfony public bundles')]
function assets(): void
{
    \dockerCompose('run --rm php rm -rf public/bundles public/js');
    \phpRun('bin/console --env=prod pim:installer:assets --symlink --clean');
}

#[AsTask(namespace: 'front', name: 'css', description: 'Compile Less to CSS')]
function css(): void
{
    \dockerCompose('run --rm php rm -rf public/css');
    \yarnRun('run less');
}

#[AsTask(namespace: 'front', name: 'javascript-prod', description: 'Build JavaScript for production')]
function javascriptProd(): void
{
    javascriptExtensions();
    \nodeRun('rm -rf public/dist');
    \yarnRun('run webpack');
}

#[AsTask(namespace: 'front', name: 'javascript-dev', description: 'Build JavaScript for development (watch mode)')]
function javascriptDev(): void
{
    javascriptExtensions();
    \nodeRun('rm -rf public/dist');
    \yarnRun('run webpack-dev');
}

#[AsTask(namespace: 'front', name: 'javascript-dev-strict', description: 'Build JavaScript for development (strict mode)')]
function javascriptDevStrict(): void
{
    javascriptExtensions();
    \nodeRun('rm -rf public/dist');
    \yarnRun('run webpack-dev-strict');
}

#[AsTask(namespace: 'front', name: 'javascript-test', description: 'Build JavaScript for test')]
function javascriptTest(): void
{
    javascriptExtensions();
    \nodeRun('rm -rf public/dist');
    \yarnRun('run webpack-test');
}

#[AsTask(namespace: 'front', name: 'front', description: 'Build all front-end (assets, css, packages, js-dev)')]
function front(): void
{
    assets();
    css();
    frontPackages();
    javascriptDev();
}
