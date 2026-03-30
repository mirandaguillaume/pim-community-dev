<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;

class SortersConfiguratorTest extends TestCase
{
    private DatagridConfiguration|MockObject $configuration;
    private ConfigurationRegistry|MockObject $registry;
    private SortersConfigurator $sut;

    protected function setUp(): void
    {
        $this->configuration = $this->createMock(DatagridConfiguration::class);
        $this->registry = $this->createMock(ConfigurationRegistry::class);
        $this->sut = new SortersConfigurator($this->registry);
        $attributes = [
        'sku' => [
        'code' => 'sku',
        'type' => 'pim_catalog_identifier',
        ],
        'name' => [
        'code' => 'name',
        'type' => 'pim_catalog_text',
        ],
        ];
        $this->configuration->method('offsetGetByPath')->with(sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY))->willReturn($attributes);
        $this->configuration->method('offsetGetByPath')->with(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY))->willReturn(['family' => ['family_config'], 'identifier' => [], 'name' => []]);
        $this->registry->method('getConfiguration')->with('pim_catalog_identifier')->willReturn(['column' => ['identifier_config'], 'sorter' => 'flexible_field']);
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_sorters(): void
    {
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn(['column' => ['text_config'], 'sorter' => 'flexible_field']);
        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'identifier');
        $this->configuration->expects($this->once())->method('offsetSetByPath')->with($columnConfPath, $this->anything());
        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'name');
        $this->configuration->expects($this->once())->method('offsetSetByPath')->with($columnConfPath, $this->anything());
        $columnConfPath = sprintf('%s', OrmSorterConfiguration::COLUMNS_PATH);
        $this->configuration->expects($this->once())->method('offsetGetByPath')->with($columnConfPath);
        $this->sut->configure($this->configuration);
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn([]);
        $columnConfPath = sprintf('%s[%s]', OrmSorterConfiguration::COLUMNS_PATH, 'identifier');
        $this->configuration->expects($this->once())->method('offsetSetByPath')->with($columnConfPath, $this->anything());
        $this->sut->shouldThrow('\LogicException')->duringConfigure($this->configuration);
    }
}
