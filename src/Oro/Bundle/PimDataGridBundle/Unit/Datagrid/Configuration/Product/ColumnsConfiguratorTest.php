<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ColumnsConfiguratorTest extends TestCase
{
    private DatagridConfiguration|MockObject $configuration;
    private ConfigurationRegistry|MockObject $registry;
    private ColumnsConfigurator $sut;

    protected function setUp(): void
    {
        $this->configuration = $this->createMock(DatagridConfiguration::class);
        $this->registry = $this->createMock(ConfigurationRegistry::class);
        $this->sut = new ColumnsConfigurator($this->registry);
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_columns(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturnMap([
                ['pim_catalog_identifier', ['column' => ['identifier_config']]],
                ['pim_catalog_text', ['column' => ['text_config']]],
            ]);

        $attributes = [
            'name' => [
                'code'          => 'name',
                'label'         => 'Name',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1,
            ],
            'desc' => [
                'code'          => 'desc',
                'label'         => 'Desc',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 3,
                'group'         => 'General',
                'groupOrder'    => 1,
            ],
        ];

        $otherColumns = [
            'parent' => [
                'code'          => 'parent',
                'label'         => 'parent',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1,
            ],
        ];

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $useableAttrPath = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $otherColumnsPath = sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY);
        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);

        $this->configuration->method('offsetGetByPath')
            ->willReturnMap([
                [$columnConfPath, null, ['family' => ['family_config']]],
                [$useableAttrPath, null, $attributes],
                [$otherColumnsPath, null, $otherColumns],
                [$displayColumnPath, null, null],
            ]);

        $this->configuration->expects($this->atLeast(1))
            ->method('offsetSetByPath');

        $this->sut->configure($this->configuration);
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturnMap([
                ['pim_catalog_identifier', ['column' => ['identifier_config']]],
                ['pim_catalog_text', []],
            ]);

        $attributes = [
            'identifier' => [
                'code'          => 'sku',
                'label'         => 'Sku',
                'type'          => 'pim_catalog_identifier',
                'sortOrder'     => 1,
                'group'         => 'General',
                'groupOrder'    => 1,
            ],
            'name' => [
                'code'          => 'name',
                'label'         => 'Name',
                'type'          => 'pim_catalog_text',
                'sortOrder'     => 2,
                'group'         => 'General',
                'groupOrder'    => 1,
            ],
        ];

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $useableAttrPath = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);

        $this->configuration->method('offsetGetByPath')
            ->willReturnMap([
                [$columnConfPath, null, ['family' => ['family_config']]],
                [$useableAttrPath, null, $attributes],
            ]);

        $this->expectException(\LogicException::class);
        $this->sut->configure($this->configuration);
    }
}
