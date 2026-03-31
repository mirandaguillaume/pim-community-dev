<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactory;
use PHPUnit\Framework\TestCase;

class SimpleFactoryTest extends TestCase
{
    private SimpleFactory $sut;

    protected function setUp(): void
    {
        $this->sut = new SimpleFactory(self::MY_CLASS);
    }

    public function test_it_creates_an_object(): void
    {
        $this->sut->create()->shouldReturnAnInstanceOf(self::MY_CLASS);
    }
}
