<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

class SetIdentifierValueTest extends TestCase
{
    private SetIdentifierValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetIdentifierValue('sku', 'my_beautiful_product');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetIdentifierValue::class, $this->sut);
        $this->assertInstanceOf(ValueUserIntent::class, $this->sut);
    }

    public function test_it_exposes_the_attribute_code(): void
    {
        $this->assertSame('sku', $this->sut->attributeCode());
    }

    public function test_it_has_a_null_locale(): void
    {
        $this->assertNull($this->sut->localeCode());
    }

    public function test_it_has_a_null_channel(): void
    {
        $this->assertNull($this->sut->channelCode());
    }

    public function test_it_exposes_its_value(): void
    {
        $this->assertSame('my_beautiful_product', $this->sut->value());
    }
}
