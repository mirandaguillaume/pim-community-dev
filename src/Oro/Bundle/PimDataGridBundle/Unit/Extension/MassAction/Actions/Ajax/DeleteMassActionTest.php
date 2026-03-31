<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Ajax\DeleteMassAction;
use PHPUnit\Framework\TestCase;

class DeleteMassActionTest extends TestCase
{
    private DeleteMassAction $sut;

    protected function setUp(): void
    {
        $this->sut = new DeleteMassAction();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DeleteMassAction::class, $this->sut);
        $this->assertInstanceOf(MassActionInterface::class, $this->sut);
    }

    public function test_it_overwrites_default_values(): void
    {
        $routeParams = ['foo' => 'bar'];
        $params = [
                    'route'            => 'baz',
                    'route_parameters' => $routeParams,
                    'handler'          => 'my_handler',
                    'confirmation'     => false,
                    'entity_name'      => 'qux',
                ];
        $options = ActionConfiguration::createNamed('export', $params);
        $this->sut->setOptions($options);
        $this->assertSame('export', $this->sut->getOptions()->getName());
        $this->assertSame($routeParams, $this->sut->getOptions()->offsetGet('route_parameters'));
        $this->assertSame('my_handler', $this->sut->getOptions()->offsetGet('handler'));
        $this->assertSame('baz', $this->sut->getOptions()->offsetGet('route'));
        $this->assertSame(false, $this->sut->getOptions()->offsetGet('confirmation'));
    }
}
