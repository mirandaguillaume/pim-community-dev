<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetSimpleSelectValueTest extends TestCase
{
    private SetSimpleSelectValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetSimpleSelectValue('size', 'ecommerce', 'en_US', 'M');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetSimpleSelectValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('size', $this->sut->attributeCode());
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
        $this->assertSame('M', $this->sut->value());
    }

    public function test_it_is_not_initializable_with_an_empty_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetSimpleSelectValue('name', 'ecommerce', 'en_US', '');
    }
}
