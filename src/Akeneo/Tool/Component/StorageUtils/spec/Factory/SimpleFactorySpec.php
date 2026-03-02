<?php

namespace spec\Akeneo\Tool\Component\StorageUtils\Factory;

use PhpSpec\ObjectBehavior;

class SimpleFactorySpec extends ObjectBehavior
{
    final public const MY_CLASS = 'stdClass';

    public function let()
    {
        $this->beConstructedWith(self::MY_CLASS);
    }

    public function it_creates_an_object()
    {
        $this->create()->shouldReturnAnInstanceOf(self::MY_CLASS);
    }
}
