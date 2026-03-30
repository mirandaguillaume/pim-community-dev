<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetMeasurementValueTest extends TestCase
{
    private SetMeasurementValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetMeasurementValue('power', 'ecommerce', 'en_US', '100', 'KILOWATT');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetMeasurementValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('power', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_amount(): void
    {
        $this->assertSame('100', $this->sut->amount());
    }

    public function test_it_returns_the_unit(): void
    {
        $this->assertSame('KILOWATT', $this->sut->unit());
    }
}
