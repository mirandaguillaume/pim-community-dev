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

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(ConversionUnitCollection::class, $this->sut);
    }

    public function testItThrowsExceptionWhenTryingToCreateItWithNonStringArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConversionUnitCollection::fromArray(['an_attribute' => 1]);
    }

    public function testItSaysIfItHasAConversionUnitOrNot(): void
    {
        $this->assertSame(true, $this->sut->hasConversionUnit('an_measurement_attribute'));
        $this->assertSame(true, $this->sut->hasConversionUnit('another_measurement_attribute'));
        $this->assertSame(false, $this->sut->hasConversionUnit('an_unknown_measurement_attribute'));
    }

    public function testItReturnsAConversionUnit(): void
    {
        $this->assertSame('GRAM', $this->sut->getConversionUnit('an_measurement_attribute'));
        $this->assertSame('POUND', $this->sut->getConversionUnit('another_measurement_attribute'));
        $this->assertNull($this->sut->getConversionUnit('an_unknown_measurement_attribute'));
    }
}
