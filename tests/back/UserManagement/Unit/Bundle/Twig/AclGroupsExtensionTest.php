<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\Bundle\Twig;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Bundle\Twig\AclGroupsExtension;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AclGroupsExtensionTest extends TestCase
{
    private AclGroupsExtension $sut;

    protected function setUp(): void
    {
        $this->sut = new AclGroupsExtension();
    }

}
