<?php

namespace spec\Akeneo\Tool\Component\Batch\Item;

use PhpSpec\ObjectBehavior;

class ExecutionContextSpec extends ObjectBehavior
{
    public function it_is_dirty()
    {
        $this->isDirty()->shouldReturn(false);
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
    }

    public function it_allows_to_change_dirty_flag()
    {
        $this->isDirty()->shouldReturn(false);
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
        $this->clearDirtyFlag();
        $this->isDirty()->shouldReturn(false);
    }

    public function it_allows_to_add_value()
    {
        $this->put('test_key', 'test_value');
        $this->isDirty()->shouldReturn(true);
        $this->get('test_key')->shouldReturn('test_value');
    }

    public function it_allows_to_remove_value()
    {
        $this->put('test_key', 'test_value');
        $this->get('test_key')->shouldReturn('test_value');
        $this->remove('test_key');
        $this->get('test_key')->shouldReturn(null);
    }

    public function it_provides_keys()
    {
        $this->put('test_key', 'test_value');
        $this->put('test_key2', 'test_value');
        $this->getKeys()->shouldReturn(['test_key', 'test_key2']);
    }
}
