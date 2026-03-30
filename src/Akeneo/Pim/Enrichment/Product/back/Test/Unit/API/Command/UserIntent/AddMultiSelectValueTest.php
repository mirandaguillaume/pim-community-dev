<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddMultiSelectValueTest extends TestCase
{
    private AddMultiSelectValue $sut;

    protected function setUp(): void
    {
        $this->sut = new AddMultiSelectValue('name', 'ecommerce', 'en_US', ['option_code_1', 'option_code_2']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddMultiSelectValue::class, $this->sut);
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

    public function test_it_returns_the_option_codes(): void
    {
        $this->assertSame(['option_code_1', 'option_code_2'], $this->sut->optionCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_option_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiSelectValue('name', 'ecommerce', 'en_US', ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_with_empty_option_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiSelectValue('name', 'ecommerce', 'en_US', []);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_option_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiSelectValue('name', 'ecommerce', 'en_US', ['a', '', 'b']);
    }
}
