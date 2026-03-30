<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Redirect\EditMassAction;

class EditMassActionTest extends TestCase
{
    private EditMassAction $sut;

    protected function setUp(): void
    {
        $this->sut = new EditMassAction();
    }

    public function test_it_requires_the_route(): void
    {
        $params = [];
        $options = ActionConfiguration::createNamed('edit', $params);
        $this->sut->shouldThrow(
            new \LogicException('There is no option "route" for action "edit".')
        )->duringSetOptions($options);
    }

    public function test_it_defines_default_values(): void
    {
        $params = ['route' => 'foo'];
        $options = ActionConfiguration::createNamed('edit', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('edit', $this->sut->getOptions()->getName());
        $this->assertSame('redirect', $this->sut->getOptions()->offsetGet('frontend_type'));
        $this->assertSame([], $this->sut->getOptions()->offsetGet('route_parameters'));
        $this->assertSame('mass_edit', $this->sut->getOptions()->offsetGet('handler'));
    }

    public function test_it_overwrites_default_values(): void
    {
        $routeParams = ['foo' => 'bar'];
        $params = [
                    'route'            => 'baz',
                    'route_parameters' => $routeParams,
                    'handler'          => 'my_handler',
                ];
        $options = ActionConfiguration::createNamed('edit', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('edit', $this->sut->getOptions()->getName());
        $this->assertSame('my_handler', $this->sut->getOptions()->offsetGet('handler'));
        $this->assertSame('baz', $this->sut->getOptions()->offsetGet('route'));
        $this->assertSame($routeParams, $this->sut->getOptions()->offsetGet('route_parameters'));
    }

    public function test_it_doesnt_allow_overriding_frontend_type(): void
    {
        $params = ['route' => 'foo', 'frontend_type' => 'bar'];
        $options = ActionConfiguration::createNamed('edit', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('redirect', $this->sut->getOptions()->offsetGet('frontend_type'));
    }
}
