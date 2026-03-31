<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetPriceValueTest extends TestCase
{
    private SetPriceValue $sut;

    protected function setUp(): void
    {
        $priceValue = new PriceValue('100', 'USD');
        $this->sut = new SetPriceValue('net_price', 'ecommerce', 'en_US', $priceValue);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetPriceValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('net_price', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_price_value(): void
    {
        $priceValue = new PriceValue('100', 'USD');
        $this->assertEquals($priceValue, $this->sut->priceValue());
    }
}
