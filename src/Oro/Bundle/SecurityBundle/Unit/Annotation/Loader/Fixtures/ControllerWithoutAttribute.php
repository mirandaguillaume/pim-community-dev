<?php

declare(strict_types=1);

// This file must not mention the attribute at all: the loader skips files whose source does not
// contain it, and that skip is what this fixture exercises.
namespace Oro\Bundle\SecurityBundle\Unit\Annotation\Loader\Fixtures;

class ControllerWithoutAttribute
{
    public function indexAction(): void
    {
    }
}
