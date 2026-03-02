<?php

namespace Specification\Akeneo\Channel\API\Query;

use PhpSpec\ObjectBehavior;

class LocaleSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'fr_FR',
            true
        );
    }

    public function it_has_getters()
    {
        $this->getCode()->shouldReturn('fr_FR');
        $this->isActivated()->shouldReturn(true);
    }
}
