<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceCollectionValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetPriceCollectionValueTest extends TestCase
{
    private SetPriceCollectionValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetPriceCollectionValue(
            'msrp',
            'ecommerce',
            'en_US',
            [
                new PriceValue(20, "EUR"),
                new PriceValue(50, "USD"),
            ]
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetPriceCollectionValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('msrp', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_price_values(): void
    {
        $priceValues = [
                    new PriceValue(20, "EUR"),
                    new PriceValue(50, "USD"),
                ];
        $this->sut = new SetPriceCollectionValue(
            'msrp',
            'ecommerce',
            'en_US',
            $priceValues
        );
        $this->assertSame($priceValues, $this->sut->priceValues());
    }

    public function test_it_cannot_be_instantiated_with_other_values_than_price_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetPriceCollectionValue(
            'msrp',
            'ecommerce',
            'en_US',
            [
                        'test',
                    ]
        );
    }
}
