<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export\ExportMassActionInterface;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Extension\MassAction\Actions\Export\ExportMassAction;

class ExportMassActionTest extends TestCase
{
    private ExportMassAction $sut;

    protected function setUp(): void
    {
        $this->sut = new ExportMassAction();
    }

    public function test_it_is_an_export_mass_action(): void
    {
        $this->sut->shouldImplement(
            ExportMassActionInterface::class
        );
    }

    public function test_it_requires_the_format_route_parameter(): void
    {
        $options = ActionConfiguration::createNamed('export', []);
        $this->sut->shouldThrow(
            new \LogicException('There is no route_parameter named "_format" for action "export"')
        )->duringSetOptions($options);
    }

    public function test_it_requires_the_content_type_route_parameter(): void
    {
        $params = [
                    'route_parameters' => ['_format' => 'foo'],
                ];
        $options = ActionConfiguration::createNamed('export', $params);
        $this->sut->shouldThrow(
            new \LogicException('There is no route_parameter named "_contentType" for action "export"')
        )->duringSetOptions($options);
    }

    public function test_it_defines_default_values(): void
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $params = ['route_parameters' => $routeParams];
        $options = ActionConfiguration::createNamed('export', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('export', $this->sut->getOptions()->getName());
        $this->assertSame('export', $this->sut->getOptions()->offsetGet('frontend_type'));
        $this->assertSame([], $this->sut->getOptions()->offsetGet('context'));
        $this->assertSame('pim_datagrid_export_index', $this->sut->getOptions()->offsetGet('route'));
        $this->assertSame($routeParams, $this->sut->getOptions()->offsetGet('route_parameters'));
        $this->assertSame('quick_export', $this->sut->getOptions()->offsetGet('handler'));
    }

    public function test_it_overwrites_default_values(): void
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $context = ['baz' => 'qux'];
        $params = [
                    'route_parameters' => $routeParams,
                    'context'          => $context,
                    'route'            => 'my_route',
                    'handler'          => 'my_handler',
                ];
        $options = ActionConfiguration::createNamed('export', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('export', $this->sut->getOptions()->getName());
        $this->assertSame($context, $this->sut->getOptions()->offsetGet('context'));
        $this->assertSame('my_route', $this->sut->getOptions()->offsetGet('route'));
        $this->assertSame($routeParams, $this->sut->getOptions()->offsetGet('route_parameters'));
        $this->assertSame('my_handler', $this->sut->getOptions()->offsetGet('handler'));
    }

    public function test_it_gets_export_context(): void
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $context = ['baz' => 'qux'];
        $params = [
                    'route_parameters' => $routeParams,
                    'context'          => $context,
                ];
        $options = ActionConfiguration::createNamed('export', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame($context, $this->sut->getExportContext());
    }

    public function test_it_doesnt_allow_overriding_frontend_type(): void
    {
        $routeParams = ['_format' => 'foo', '_contentType' => 'bar'];
        $params = ['route_parameters' => $routeParams, 'frontend_type' => 'bar'];
        $options = ActionConfiguration::createNamed('edit', $params);
        $this->sut->setOptions($options)->shouldNotThrow($this->anything());
        $this->assertSame('export', $this->sut->getOptions()->offsetGet('frontend_type'));
    }
}
