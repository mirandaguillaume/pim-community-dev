<?php

namespace spec\Akeneo\Tool\Component\Batch\Item;

use Akeneo\Tool\Component\Batch\Item\InvalidItemInterface;
use PhpSpec\ObjectBehavior;

class InvalidItemExceptionSpec extends ObjectBehavior
{
    public function let(InvalidItemInterface $invalidItem)
    {
        $invalidItem->getInvalidData()->willReturn(['foo' => 'fighter']);

        $this->beConstructedWith(
            'Tango is down, I repeat...',
            $invalidItem
        );
    }

    public function it_provides_the_message()
    {
        $this->getMessage()->shouldReturn('Tango is down, I repeat...');
    }

    public function it_provides_the_invalid_item($invalidItem)
    {
        $this->getItem()->shouldReturn($invalidItem);
    }
}
