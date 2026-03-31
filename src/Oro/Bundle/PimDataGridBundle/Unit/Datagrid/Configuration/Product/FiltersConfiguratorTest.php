<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\FiltersConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $configuration->method('offsetGet')->willReturnMap([
            [ContextConfigurator::SOURCE_KEY, $attributesConf],
            [FilterConfiguration::FILTERS_KEY, []],
        ]);
        $this->registry->method('getConfiguration')->willReturnMap([
            ['pim_catalog_identifier', ['filter' => ['identifier_config']]],
            ['pim_catalog_text', ['filter' => ['text_config']]],
        ]);
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

    public function test_it_sorts_filters_by_group_order_then_sort_order(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $attributes = [
            'attr_c' => [
                'code'                => 'attr_c',
                'label'               => 'Attr C',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 3,
                'group'               => 'Marketing',
                'groupOrder'          => 2,
            ],
            'attr_a' => [
                'code'                => 'attr_a',
                'label'               => 'Attr A',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 1,
                'group'               => 'General',
                'groupOrder'          => 1,
            ],
            'attr_b' => [
                'code'                => 'attr_b',
                'label'               => 'Attr B',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 5,
                'group'               => 'General',
                'groupOrder'          => 1,
            ],
            'attr_d' => [
                'code'                => 'attr_d',
                'label'               => 'Attr D',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 1,
                'group'               => 'Other',
                'groupOrder'          => null,
            ],
        ];

        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->method('offsetGet')->willReturnMap([
            [ContextConfigurator::SOURCE_KEY, $attributesConf],
            [FilterConfiguration::FILTERS_KEY, []],
        ]);
        $this->registry->method('getConfiguration')->willReturn(['filter' => ['text_config']]);

        $capturedFilters = null;
        $configuration->expects($this->once())->method('offsetSet')
            ->willReturnCallback(function ($key, $value) use (&$capturedFilters) {
                $capturedFilters = $value;
            });

        $this->sut->configure($configuration);

        $this->assertNotNull($capturedFilters);
        $filterCodes = array_keys($capturedFilters['columns']);

        // Group 1 (General) should come first, then Group 2 (Marketing), then null group
        // Within same group, sort by sortOrder
        $this->assertSame('attr_a', $filterCodes[0]); // groupOrder=1, sortOrder=1
        $this->assertSame('attr_b', $filterCodes[1]); // groupOrder=1, sortOrder=5
        $this->assertSame('attr_c', $filterCodes[2]); // groupOrder=2, sortOrder=3
        $this->assertSame('attr_d', $filterCodes[3]); // groupOrder=null => last
    }

    public function test_it_skips_non_useable_attributes(): void
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
            'hidden' => [
                'code'                => 'hidden',
                'label'               => 'Hidden',
                'useableAsGridFilter' => 0,
                'type'                => 'pim_catalog_text',
                'sortOrder'           => 2,
                'group'               => 'General',
                'groupOrder'          => 1,
            ],
        ];

        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->method('offsetGet')->willReturnMap([
            [ContextConfigurator::SOURCE_KEY, $attributesConf],
            [FilterConfiguration::FILTERS_KEY, []],
        ]);
        $this->registry->method('getConfiguration')->willReturn(['filter' => ['config']]);

        $capturedFilters = null;
        $configuration->expects($this->once())->method('offsetSet')
            ->willReturnCallback(function ($key, $value) use (&$capturedFilters) {
                $capturedFilters = $value;
            });

        $this->sut->configure($configuration);

        $filterCodes = array_keys($capturedFilters['columns']);
        $this->assertSame(['sku'], $filterCodes);
    }

    public function test_it_adds_metric_family_for_metric_type(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $attributes = [
            'weight' => [
                'code'                => 'weight',
                'label'               => 'Weight',
                'useableAsGridFilter' => 1,
                'type'                => 'pim_catalog_metric',
                'sortOrder'           => 1,
                'group'               => 'General',
                'groupOrder'          => 1,
                'metricFamily'        => 'Weight',
            ],
        ];

        $attributesConf = [ContextConfigurator::USEABLE_ATTRIBUTES_KEY => $attributes];
        $configuration->method('offsetGet')->willReturnMap([
            [ContextConfigurator::SOURCE_KEY, $attributesConf],
            [FilterConfiguration::FILTERS_KEY, []],
        ]);
        $this->registry->method('getConfiguration')->willReturn(['filter' => ['metric_config']]);

        $capturedFilters = null;
        $configuration->expects($this->once())->method('offsetSet')
            ->willReturnCallback(function ($key, $value) use (&$capturedFilters) {
                $capturedFilters = $value;
            });

        $this->sut->configure($configuration);

        $this->assertArrayHasKey('weight', $capturedFilters['columns']);
        $this->assertSame('Weight', $capturedFilters['columns']['weight']['family']);
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
        $configuration->method('offsetGet')->willReturnMap([
            [ContextConfigurator::SOURCE_KEY, $attributesConf],
        ]);
        $this->registry->method('getConfiguration')->willReturnMap([
            ['pim_catalog_identifier', ['filter' => ['identifier_config']]],
            ['pim_catalog_text', []],
        ]);
        $this->expectException(\LogicException::class);
        $this->sut->configure($configuration);
    }
}
