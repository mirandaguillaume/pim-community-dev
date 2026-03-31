<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\ProductValueLocaleFilter;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductValueLocaleFilterTest extends TestCase
{
    private ProductValueLocaleFilter $sut;

    protected function setUp(): void
    {
        $this->sut = new ProductValueLocaleFilter();
    }

    public function test_it_does_not_filter_a_product_value_if_locale_option_is_empty(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('getLocaleCode')->willReturn(null);
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', []));
    }

    public function test_it_filters_a_product_value_if_it_is_not_in_locales_option(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isLocalizable')->willReturn(true);
        $price->method('getLocaleCode')->willReturn('fr_FR');
        $this->assertSame(true, $this->sut->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']]));
    }

    public function test_it_does_not_filter_a_product_value_if_it_is_in_locales_options(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isLocalizable')->willReturn(false);
        $price->method('getLocaleCode')->willReturn('fr_FR');
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US', 'fr_FR']]));
    }

    public function test_it_does_not_filter_a_product_value_if_it_is_not_scopable(): void
    {
        $price = $this->createMock(ValueInterface::class);

        $price->method('isLocalizable')->willReturn(false);
        $this->assertSame(false, $this->sut->filterObject($price, 'pim:product_value:view', ['locales' => ['en_US']]));
    }

    public function test_it_fails_if_it_is_not_a_product_value(): void
    {
        $anOtherObject = new \stdClass();

        $this->expectException('\LogicException');
        $this->sut->filterObject($anOtherObject, 'pim:product_value:view', ['locales' => ['en_US']]);
    }
}
