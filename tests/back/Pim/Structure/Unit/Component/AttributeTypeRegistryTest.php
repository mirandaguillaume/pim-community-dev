<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use PHPUnit\Framework\TestCase;

class AttributeTypeRegistryTest extends TestCase
{
    private AttributeTypeRegistry $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeTypeRegistry();
    }

}
