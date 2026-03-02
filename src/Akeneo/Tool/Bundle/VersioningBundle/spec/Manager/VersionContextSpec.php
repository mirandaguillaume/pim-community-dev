<?php

namespace spec\Akeneo\Tool\Bundle\VersioningBundle\Manager;

use PhpSpec\ObjectBehavior;

class VersionContextSpec extends ObjectBehavior
{
    public function it_adds_and_returns_a_default_context()
    {
        $this->addContextInfo('my super context');
        $this->getContextInfo()->shouldReturn('my super context');
    }
    public function it_adds_and_returns_a_context_with_fqcn()
    {
        $this->addContextInfo('my super context with fqcn', 'MyClass');
        $this->getContextInfo('MyClass')->shouldReturn('my super context with fqcn');
    }
}
