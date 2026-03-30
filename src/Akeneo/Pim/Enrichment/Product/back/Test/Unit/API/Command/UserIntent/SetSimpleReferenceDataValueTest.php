<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceDataValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetSimpleReferenceDataValueTest extends TestCase
{
    private SetSimpleReferenceDataValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetSimpleReferenceDataValue('attribute_name', null, null, 'Akeneo');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetSimpleReferenceDataValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_returns_the_attribute_code(): void
    {
        $this->assertSame('attribute_name', $this->sut->attributeCode());
    }

    public function test_it_returns_the_locale_code(): void
    {
        $this->assertNull($this->sut->localeCode());
    }

    public function test_it_returns_the_channel_code(): void
    {
        $this->assertNull($this->sut->channelCode());
    }

    public function test_it_returns_the_value(): void
    {
        $this->assertSame('Akeneo', $this->sut->value());
    }

    public function test_it_cannot_be_instantiated_with_an_empty_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SetSimpleReferenceDataValue('attribute_name', null, null, '');
    }
}
