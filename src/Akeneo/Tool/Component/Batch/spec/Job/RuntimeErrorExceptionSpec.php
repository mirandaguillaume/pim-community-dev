<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use PhpSpec\ObjectBehavior;

class RuntimeErrorExceptionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('my message %myparam%', ['%myparam%' => 'param']);
    }

    public function it_provides_message_parameters()
    {
        $this->getMessageParameters()->shouldReturn(['%myparam%' => 'param']);
    }
}
