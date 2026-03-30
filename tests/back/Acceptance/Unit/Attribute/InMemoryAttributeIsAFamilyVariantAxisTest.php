<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\AttributeIsAFamilyVariantAxisInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeIsAFamilyVariantAxis;
use PHPUnit\Framework\TestCase;

class InMemoryAttributeIsAFamilyVariantAxisTest extends TestCase
{
    private InMemoryAttributeIsAFamilyVariantAxis $sut;

    protected function setUp(): void
    {
        $this->sut = new InMemoryAttributeIsAFamilyVariantAxis();
    }

    public function test_it_is_a_query_to_check_attribute_is_a_family_variant_axis(): void
    {
        $this->assertInstanceOf(AttributeIsAFamilyVariantAxisInterface::class, $this->sut);
    }

    public function test_it_is_an_in_memory_query(): void
    {
        $this->assertInstanceOf(InMemoryAttributeIsAFamilyVariantAxis::class, $this->sut);
    }

    public function test_it_returns_false_when_memory_is_empty(): void
    {
        $this->assertSame(false, $this->sut->execute('someAttributeCode'));
    }

    public function test_it_returns_in_memory_values_for_family_variant_axis(): void
    {
        $this->sut->setAxisAttribute('attributeA', false);
        $this->sut->setAxisAttribute('attributeB', true);
        $this->assertSame(false, $this->sut->execute('attributeA'));
        $this->assertSame(true, $this->sut->execute('attributeB'));
        $this->assertSame(false, $this->sut->execute('attributeC'));
    }
}
