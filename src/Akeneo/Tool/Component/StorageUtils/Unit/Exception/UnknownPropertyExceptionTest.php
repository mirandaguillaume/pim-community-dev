<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use PHPUnit\Framework\TestCase;

class UnknownPropertyExceptionTest extends TestCase
{
    private UnknownPropertyException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_unknown_property_exception(): void
    {
        $previous = new \Exception();
        $exception = UnknownPropertyException::unknownProperty('property', $previous);
        $this->sut = new UnknownPropertyException(
            'property',
            'Property "property" does not exist.',
            0,
            $previous
        );
        $this->assertTrue(is_a(UnknownPropertyException::class, $exception::class, true));
        $this->assertSame('property', $this->sut->getPropertyName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
        $this->assertSame($exception->getPrevious(), $this->sut->getPrevious());
    }
}
