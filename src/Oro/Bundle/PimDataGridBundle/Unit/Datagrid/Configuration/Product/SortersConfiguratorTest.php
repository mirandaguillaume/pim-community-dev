<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->configuration->method('offsetGetByPath')->willReturnCallback(function (string $path, $default = null) use ($attributes) {
            return match ($path) {
                sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY) => $attributes,
                sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY) => ['family' => ['family_config'], 'identifier' => [], 'name' => []],
                default => $default,
            };
        });
        $this->registry->method('getConfiguration')->willReturnCallback(function (string $type) {
            return match ($type) {
                'pim_catalog_identifier' => ['column' => ['identifier_config'], 'sorter' => 'flexible_field'],
                'pim_catalog_text' => ['column' => ['text_config'], 'sorter' => 'flexible_field'],
                default => [],
            };
        });
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_sorters(): void
    {
        $this->configuration->expects($this->atLeastOnce())->method('offsetSetByPath');
        $this->sut->configure($this->configuration);
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        // Override the registry to return empty config for text
        $this->registry = $this->createMock(ConfigurationRegistry::class);
        $this->registry->method('getConfiguration')->willReturnCallback(function (string $type) {
            return match ($type) {
                'pim_catalog_identifier' => ['column' => ['identifier_config'], 'sorter' => 'flexible_field'],
                default => [],
            };
        });
        $this->sut = new SortersConfigurator($this->registry);

        $attributes = [
            'sku' => ['code' => 'sku', 'type' => 'pim_catalog_identifier'],
            'name' => ['code' => 'name', 'type' => 'pim_catalog_text'],
        ];
        $configuration = $this->createMock(DatagridConfiguration::class);
        $configuration->method('offsetGetByPath')->willReturnCallback(function (string $path, $default = null) use ($attributes) {
            return match ($path) {
                sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY) => $attributes,
                sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY) => ['family' => ['family_config'], 'identifier' => [], 'name' => []],
                default => $default,
            };
        });
        $configuration->expects($this->atLeastOnce())->method('offsetSetByPath');
        $this->expectException(\LogicException::class);
        $this->sut->configure($configuration);
    }
}
