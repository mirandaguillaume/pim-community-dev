<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\MandatoryPropertyException;
use PHPUnit\Framework\TestCase;

class MandatoryPropertyExceptionTest extends TestCase
{
    private MandatoryPropertyException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_a_mandatory_property_exception(): void
    {
        $exception = MandatoryPropertyException::mandatoryProperty(
            'property',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\FamilyVariant'
        );
        $this->sut = new MandatoryPropertyException(
            'property',
            'Akeneo\Pim\Enrichment\Component\Product\Updater\FamilyVariant',
            'Property "property" is mandatory.',
            0
        );
        $this->assertTrue(is_a(MandatoryPropertyException::class, $exception::class, true));
        $this->assertSame('property', $this->sut->getPropertyName());
        $this->assertSame($exception->getClassName(), $this->sut->getClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }
}
