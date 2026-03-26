<?php

declare(strict_types=1);

namespace Akeneo\Test\Channel\Unit\API\Query;

use Akeneo\Channel\API\Query\ConversionUnitCollection;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class ConversionUnitCollectionTest extends TestCase
{
    private ConversionUnitCollection $sut;

    protected function setUp(): void
    {
        $this->sut = ConversionUnitCollection::fromArray(['an_measurement_attribute' => 'GRAM', 'another_measurement_attribute' => 'POUND']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConversionUnitCollection::class, $this->sut);
    }

    public function test_it_throws_exception_when_trying_to_create_it_with_non_string_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConversionUnitCollection::fromArray(['an_attribute' => 1]);
    }

    public function test_it_says_if_it_has_a_conversion_unit_or_not(): void
    {
        $this->assertSame(true, $this->sut->hasConversionUnit('an_measurement_attribute'));
        $this->assertSame(true, $this->sut->hasConversionUnit('another_measurement_attribute'));
        $this->assertSame(false, $this->sut->hasConversionUnit('an_unknown_measurement_attribute'));
    }

    public function test_it_returns_a_conversion_unit(): void
    {
        $this->assertSame('GRAM', $this->sut->getConversionUnit('an_measurement_attribute'));
        $this->assertSame('POUND', $this->sut->getConversionUnit('another_measurement_attribute'));
        $this->assertNull($this->sut->getConversionUnit('an_unknown_measurement_attribute'));
    }
}
