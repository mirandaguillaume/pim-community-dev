<?php

namespace spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Event;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\EventDispatcher\Event;

class MassActionEventSpec extends ObjectBehavior
{
    public function let(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $this->beConstructedWith($datagrid, $massAction, ['foo']);
    }

    public function it_is_an_event()
    {
        $this->shouldBeAnInstanceOf(Event::class);
    }

    public function it_returns_datagrid($datagrid)
    {
        $this->getDatagrid()->shouldReturn($datagrid);
    }

    public function it_returns_mass_action($massAction)
    {
        $this->getMassAction()->shouldReturn($massAction);
    }

    public function it_returns_objects($objects)
    {
        $this->getObjects()->shouldReturn(['foo']);
    }
}
