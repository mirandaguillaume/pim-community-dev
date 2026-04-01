<?php

/**
 * Structure bounded context tasks.
 */

namespace structure;

use Castor\Attribute\AsTask;

#[AsTask(namespace: 'structure', name: 'coupling-back', description: 'Run coupling detector for structure')]
function couplingBack(): void
{
    \couplingDetector('src/Akeneo/Pim/Structure/.php_cd.php', 'src/Akeneo/Pim/Structure');
}
