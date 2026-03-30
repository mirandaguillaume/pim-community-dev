<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Form\Transformer;

use Akeneo\UserManagement\Bundle\Form\Transformer\AccessLevelToBooleanTransformer;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\DataTransformerInterface;

class AccessLevelToBooleanTransformerTest extends TestCase
{
    private AccessLevelToBooleanTransformer $sut;

    protected function setUp(): void
    {
        $this->sut = new AccessLevelToBooleanTransformer();
    }

}
