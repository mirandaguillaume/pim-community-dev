<?php

declare(strict_types=1);

// Namespace deliberately matches the PSR-4 prefix `Oro\` -> `src/Oro`, unlike the test class next
// door: the loader reflects these classes by name, so they have to be autoloadable.
namespace Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ControllerWithMethodAttribute
{
    #[AclAncestor('acl_bound_to_a_method')]
    public function indexAction(): void
    {
    }

    public function unguardedAction(): void
    {
    }
}
