<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeType\PriceCollectionType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PHPUnit\Framework\TestCase;

class PriceCollectionTypeTest extends TestCase
{
    private PriceCollectionType $sut;

    protected function setUp(): void
    {
        $this->sut = new PriceCollectionType();
    }

}
