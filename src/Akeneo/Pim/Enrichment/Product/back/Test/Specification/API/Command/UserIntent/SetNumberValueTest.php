<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetNumberValueTest extends TestCase
{
    private SetNumberValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetNumberValue('name', 'ecommerce', 'en_US', '10');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetNumberValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('name', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertSame('en_US', $this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertSame('ecommerce', $this->sut->channelCode());
    }

    public function test_it_returns_the_value(): void
    {
        $this->assertSame('10', $this->sut->value());
    }

    public function test_it_accepts_int_string_as_value(): void
    {
        $this->sut = new SetNumberValue('name_string_int', 'ecommerce', 'en_US', '33');
        $this->assertSame('33', $this->sut->value());
    }

    public function test_it_accepts_float_string_as_value(): void
    {
        $this->sut = new SetNumberValue('name_string_float', 'ecommerce', 'en_US', '33.33');
        $this->assertSame('33.33', $this->sut->value());
    }
}
