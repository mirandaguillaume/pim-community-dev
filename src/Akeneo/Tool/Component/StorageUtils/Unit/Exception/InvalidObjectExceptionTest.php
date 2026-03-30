<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use PHPUnit\Framework\TestCase;

class InvalidObjectExceptionTest extends TestCase
{
    private InvalidObjectException $sut;

    protected function setUp(): void
    {
    }

    public function test_it_creates_an_immutable_property_exception(): void
    {
        $exception = InvalidObjectException::objectExpected('stdClass', 'ProductInterface');
        $this->sut = new InvalidObjectException(
            'stdClass',
            'ProductInterface',
            'Expects a "ProductInterface", "stdClass" given.',
            0
        );
        $this->assertTrue(is_a(InvalidObjectException::class, $exception::class, true));
        $this->assertSame($exception->getObjectClassName(), $this->sut->getObjectClassName());
        $this->assertSame($exception->getExpectedClassName(), $this->sut->getExpectedClassName());
        $this->assertSame($exception->getMessage(), $this->sut->getMessage());
        $this->assertSame($exception->getCode(), $this->sut->getCode());
    }
}
