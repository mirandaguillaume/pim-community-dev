<?php

namespace spec\Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use PhpSpec\ObjectBehavior;

class EchoWriterSpec extends ObjectBehavior
{
    public function it_writes()
    {
        $this->write([])->shouldReturn(null);
    }
}
