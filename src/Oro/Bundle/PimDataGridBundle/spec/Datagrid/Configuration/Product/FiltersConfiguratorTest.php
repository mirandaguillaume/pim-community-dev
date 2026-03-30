<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;

class FiltersConfiguratorTest extends TestCase
{
    private ConfigurationRegistry|MockObject $registry;
    private FiltersConfigurator $sut;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ConfigurationRegistry::class);
        $this->sut = new FiltersConfigurator($this->registry);
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_filters(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $attributes = [
                    'sku' => [
                        'code'                => 'sku',
                        'label'               => 'Sku',
                        'useableAsGridFilter' => 1,
                        'type'                => 'pim_catalog_identifier',
                        'sortOrder'           => 1,
                        'group'               => 'General',
                        'groupOrder'          => 1,
                    ],
                    123456 => [
                        'code'                => '123456',
                        'label'               => 'Name',
                        'useableAsGridFilter' => 1,
                        'type'                => 'pim_catalog_text',
                        'sortOrder'           => 2,
                        'group'               => 'General',
                        'groupOrder'          => 1,
                    ],
                ];
        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->method('offsetGet')->with(ContextConfigurator::SOURCE_KEY)->willReturn($attributesConf);
        $configuration->method('offsetGet')->with(FilterConfiguration::FILTERS_KEY)->willReturn([]);
        $this->registry->method('getConfiguration')->with('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn(['filter' => ['text_config']]);
        $expectedConf = [
                    'sku' => [
                        0            => 'identifier_config',
                        'data_name'  => 'sku',
                        'label'      => 'Sku',
                        'enabled'    => false,
                        'order'      => 1,
                        'group'      => 'General',
                        'groupOrder' => 1,
                    ],
                    '123456' => [
                        0            => 'text_config',
                        'data_name'  => '123456',
                        'label'      => 'Name',
                        'enabled'    => false,
                        'order'      => 2,
                        'group'      => 'General',
                        'groupOrder' => 1,
                    ],
                ];
        $configuration->expects($this->once())->method('offsetSet')->with(FilterConfiguration::FILTERS_KEY, [
                    'columns' => $expectedConf,
                ]);
        $this->sut->configure($configuration);
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $attributes = [
                    'sku' => [
                        'code'                => 'sku',
                        'label'               => 'Sku',
                        'useableAsGridFilter' => 1,
                        'type'                => 'pim_catalog_identifier',
                        'sortOrder'           => 2,
                        'group'               => 'Foo',
                        'groupOrder'          => 3,
                    ],
                    'name' => [
                        'code'                => 'name',
                        'label'               => 'Name',
                        'useableAsGridFilter' => 1,
                        'type'                => 'pim_catalog_text',
                        'sortOrder'           => 4,
                        'group'               => 'Bar',
                        'groupOrder'          => 5,
                    ],
                ];
        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->method('offsetGet')->with(ContextConfigurator::SOURCE_KEY)->willReturn($attributesConf);
        $this->registry->method('getConfiguration')->with('pim_catalog_identifier')->willReturn(['filter' => ['identifier_config']]);
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn([]);
        $this->sut->shouldThrow('\LogicException')->duringConfigure($configuration);
    }
}
