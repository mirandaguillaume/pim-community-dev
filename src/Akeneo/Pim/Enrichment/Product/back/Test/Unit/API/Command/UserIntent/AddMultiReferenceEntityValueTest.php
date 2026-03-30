<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddMultiReferenceEntityValueTest extends TestCase
{
    private AddMultiReferenceEntityValue $sut;

    protected function setUp(): void
    {
        $this->sut = new AddMultiReferenceEntityValue('attribute_ref_entity', null, null, ['Akeneo', 'Ziggy']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(AddMultiReferenceEntityValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('attribute_ref_entity', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertNull($this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertNull($this->sut->channelCode());
    }

    public function test_it_returns_the_record_codes(): void
    {
        $this->assertSame(['Akeneo', 'Ziggy'], $this->sut->recordCodes());
    }

    public function test_it_can_only_be_instantiated_with_string_record_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiReferenceEntityValue('attribute_ref_entity', null, null, ['test', 12, false]);
    }

    public function test_it_cannot_be_instantiated_with_empty_record_codes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiReferenceEntityValue('attribute_ref_entity', null, null, []);
    }

    public function test_it_cannot_be_instantiated_if_one_of_the_record_codes_is_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AddMultiReferenceEntityValue('attribute_ref_entity', null, null, ['a', '', 'b']);
    }
}
