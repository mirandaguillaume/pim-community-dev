<?php

/**
 * Communication Channel bounded context tasks.
 * Replaces make-file/communication-channel.mk.
 */

namespace communication_channel;

use Castor\Attribute\AsTask;

use function Castor\run;

#[AsTask(namespace: 'communication-channel', name: 'lint-back', description: 'Run PHPStan for communication channel')]
function lintBack(): void
{
    \phpRun('vendor/bin/phpstan analyse --level=8 --error-format=github src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Application src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Domain');
    \phpRun('vendor/bin/phpstan analyse --level=5 --error-format=github src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Infrastructure');
}

#[AsTask(namespace: 'communication-channel', name: 'coupling-back', description: 'Run coupling detector for communication channel')]
function couplingBack(): void
{
    \couplingDetector(
        'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/.php_cd.php',
        'src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back'
    );
}

#[AsTask(namespace: 'communication-channel', name: 'unit-back', description: 'Run PHPSpec for communication channel')]
function unitBack(): void
{
    \phpRun('vendor/bin/phpspec run src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/Unit/spec/');
}

#[AsTask(namespace: 'communication-channel', name: 'integration-back', description: 'Run integration tests for communication channel')]
function integrationBack(): void
{
    if (\isCI()) {
        run('.github/scripts/run_phpunit.sh . .github/scripts/find_phpunit.php Akeneo_Communication_Channel_Integration');
    } else {
        \appEnvRun('test', 'vendor/bin/phpunit -c . --testsuite Akeneo_Communication_Channel_Integration');
    }
}

#[AsTask(namespace: 'communication-channel', name: 'unit-front', description: 'Run front-end unit tests for communication channel')]
function unitFront(): void
{
    \yarnRun('unit --coverage=false src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/tests/front/unit');
}

#[AsTask(namespace: 'communication-channel', name: 'back', description: 'Run all backend checks for communication channel')]
function back(): void
{
    \dockerCompose('run --rm php rm -rf var/cache/dev');
    run('APP_ENV=dev docker-compose run -e APP_DEBUG=1 --rm php bin/console cache:warmup');
    \phpRun('tools/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back');
    lintBack();
    couplingBack();
    unitBack();
    integrationBack();
}

#[AsTask(namespace: 'communication-channel', name: 'front', description: 'Run front-end checks for communication channel')]
function front(): void
{
    \nodeRun('node_modules/.bin/prettier --config .prettierrc.json --parser typescript --write "./src/Akeneo/Platform/Bundle/CommunicationChannelBundle/**/*.{ts,tsx}"');
    \yarnRun('unit');
}

#[AsTask(namespace: 'communication-channel', name: 'generate-models', description: 'Generate TypeScript models for communication channel')]
function generateModels(): void
{
    run(
        'docker-compose run -u node --rm'
        . ' -e YARN_REGISTRY'
        . ' -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1'
        . ' -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome'
        . ' node yarn run'
        . ' --cwd=src/Akeneo/Platform/Bundle/CommunicationChannelBundle/Resources/workspaces/communication-channel/'
        . ' generate-models'
    );
}
