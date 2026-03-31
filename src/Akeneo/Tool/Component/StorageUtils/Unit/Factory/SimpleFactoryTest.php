<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use PHPUnit\Framework\TestCase;

class SimpleFactoryTest extends TestCase
{
    private const MY_CLASS = \stdClass::class;

    private SimpleFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleFactory(self::MY_CLASS);
    }

    public function test_it_creates_an_object(): void
    {
        $this->assertInstanceOf(self::MY_CLASS, $this->sut->create());
    }
}
