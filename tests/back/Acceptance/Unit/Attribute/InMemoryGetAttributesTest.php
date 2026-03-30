<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\Attribute\InMemoryGetAttributes;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use PHPUnit\Framework\TestCase;

class InMemoryGetAttributesTest extends TestCase
{
    private InMemoryGetAttributes $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryGetAttributes();
    }

}
