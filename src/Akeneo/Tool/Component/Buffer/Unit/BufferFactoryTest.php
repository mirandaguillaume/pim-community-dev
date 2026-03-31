<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\BufferFactory;
use Akeneo\Tool\Component\Buffer\Exception\InvalidClassNameException;
use Akeneo\Tool\Component\Buffer\JSONFileBuffer;
use PHPUnit\Framework\TestCase;

class BufferFactoryTest extends TestCase
{
    private const string JSON_BUFFER_CLASS = JSONFileBuffer::class;
    private BufferFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new BufferFactory(self::JSON_BUFFER_CLASS);
    }

    public function test_it_throws_an_exception_if_configured_with_a_wrong_classname(): void
    {
        $this->expectException(InvalidClassNameException::class);
        new BufferFactory('\\stdClass');
    }

    public function test_it_creates_a_buffer(): void
    {
        $this->assertInstanceOf(self::JSON_BUFFER_CLASS, $this->sut->create());
    }
}
