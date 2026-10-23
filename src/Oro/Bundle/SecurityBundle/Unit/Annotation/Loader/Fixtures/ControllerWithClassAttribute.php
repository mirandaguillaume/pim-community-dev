<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

#[AclAncestor('acl_bound_to_a_class')]
class ControllerWithClassAttribute
{
    public function indexAction(): void
    {
    }
}
