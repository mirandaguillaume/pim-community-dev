<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;
use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PHPUnit\Framework\TestCase;

class AttributeFactoryTest extends TestCase
{
    private AttributeFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new AttributeFactory();
    }

}
