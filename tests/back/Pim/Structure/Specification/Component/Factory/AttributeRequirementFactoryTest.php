<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Factory;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Factory\AttributeRequirementFactory;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use PHPUnit\Framework\TestCase;

class AttributeRequirementFactoryTest extends TestCase
{
    private AttributeRequirementFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeRequirementFactory();
    }

}
