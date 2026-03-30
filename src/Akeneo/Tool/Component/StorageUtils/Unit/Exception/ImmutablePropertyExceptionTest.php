<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use PHPUnit\Framework\TestCase;

class ImmutablePropertyExceptionTest extends TestCase
{
    private ImmutablePropertyException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_immutable_property_exception(): void
    {
        $exception = ImmutablePropertyException::immutableProperty(
            'property',
            'property_value',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute'
        );
        $this->sut = new ImmutablePropertyException(
            'property',
            'property_value',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\Attribute',
            'Property "property" cannot be modified, "property_value" given.',
            0
        );
        $this->assertTrue(is_a(ImmutablePropertyException::class, $exception::class, true));
        $this->assertSame('property', $this->sut->getPropertyName());
        $this->assertSame($exception->getPropertyValue(), $this->sut->getPropertyValue());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }
}
