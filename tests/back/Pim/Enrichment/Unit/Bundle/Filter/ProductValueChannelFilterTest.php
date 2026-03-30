<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\ProductValueChannelFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductValueChannelFilterTest extends TestCase
{
    private ProductValueChannelFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValueChannelFilter();
    }

    public function test_it_does_not_filter_a_product_value_if_channel_option_is_empty(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('getLocaleCode')->willReturn(null);
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', []));
    }

    public function test_it_filters_a_product_value_if_it_is_not_in_channels_option(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isScopable')->willReturn(true);
        $price->method('getScopeCode')->willReturn('fr_FR');
        $this->assertSame(true, $this->sut->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']]));
    }

    public function test_it_does_not_filter_a_product_value_if_it_is_in_channels_options(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isScopable')->willReturn(false);
        $price->method('getScopeCode')->willReturn('fr_FR');
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US', 'fr_FR']]));
    }

    public function test_it_does_not_filter_a_product_value_if_it_is_not_scopable(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isScopable')->willReturn(false);
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', ['channels' => ['en_US']]));
    }

    public function test_it_fails_if_it_is_not_a_product_value(): void
    {
        $anOtherObject = $this->createMock(StdClass::class);

        $this->expectException('\LogicException');
        $this->sut->filterObject($anOtherObject, 'pim:product_value:view', ['channels' => ['en_US']]);
    }
}
