<?php

/**
 * Enrichment (legacy) bounded context tasks.
 * Replaces make-file/enrichment.mk.
 */

namespace enrichment;

use Castor\Attribute\AsTask;

#[AsTask(namespace: 'enrichment', name: 'coupling-back', description: 'Run coupling detector for enrichment')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/Pim/Enrichment/.php_cd.php', 'src/Akeneo/Pim/Enrichment');
}

#[AsTask(namespace: 'enrichment', name: 'lint-back', description: 'Run PHPStan for enrichment')]
function lintBack(): void
{
    \phpRun('vendor/bin/phpstan analyse --level=2 src/Akeneo/Pim/Enrichment --error-format=github');
}
