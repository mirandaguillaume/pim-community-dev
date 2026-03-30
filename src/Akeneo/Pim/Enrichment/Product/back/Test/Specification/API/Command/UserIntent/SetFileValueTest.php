<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFileValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use PHPUnit\Framework\TestCase;

class SetFileValueTest extends TestCase
{
    private SetFileValue $sut;

    protected function setUp(): void
    {
        $this->sut = new SetFileValue('name', 'ecommerce', 'en_US', '/path/to/file.txt');
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SetFileValue::class, $this->sut);
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
        $this->assertSame('/path/to/file.txt', $this->sut->value());
    }
}
