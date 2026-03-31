<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetMultiSelectValueTest extends TestCase
{
    private SetMultiSelectValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetMultiSelectValue('tags', 'ecommerce', 'en_US', ['uno', 'dos', 'tres']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetMultiSelectValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('tags', $this->sut->attributeCode());
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
        $this->assertSame(['uno', 'dos', 'tres'], $this->sut->values());
    }

    public function test_it_can_only_be_instanced_with_string_values(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetMultiSelectValue('name', 'ecommerce', 'en_US', ['test', 12, false]);
    }

    public function test_it_cannot_be_instanced_with_empty_values_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetMultiSelectValue('name', 'ecommerce', 'en_US', []);
    }

    public function test_it_cannot_be_instanced_if_one_of_the_values_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetMultiSelectValue('name', 'ecommerce', 'en_US', ['a', '', 'b']);
    }
}
