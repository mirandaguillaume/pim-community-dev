<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\Exception\InvalidClassNameException;
use Akeneo\Tool\Component\Buffer\JSONFileBuffer;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Buffer\BufferFactory;

class BufferFactoryTest extends TestCase
{
    private BufferFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new BufferFactory(self::JSON_BUFFER_CLASS);
    }

    public function test_it_throws_an_exception_if_configured_with_a_wrong_classname(): void
    {
        $this->expectException(InvalidClassNameException::class);
        $this->sut->__construct('\\stdClass');
    }

    public function test_it_creates_a_buffer(): void
    {
        $this->sut->create()->shouldReturnAnInstanceOf(self::JSON_BUFFER_CLASS);
    }
}
