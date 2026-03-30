<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ColumnsConfigurator;

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
        $this->registry->method('getConfiguration')->with('pim_catalog_identifier')->willReturn(['column' => ['identifier_config']]);
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn(['column' => ['text_config']]);
        $this->configuration->method('offsetGetByPath')->with(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY))->willReturn(['family' => ['family_config']]);
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_columns(): void
    {
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
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $this->configuration->method('offsetGetByPath')->with($path)->willReturn($attributes);
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
        $path = sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY);
        $this->configuration->method('offsetGetByPath')->with($path)->willReturn($otherColumns);
        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
        $this->configuration->expects($this->once())->method('offsetGetByPath')->with($displayColumnPath);
        $availableColumns = [
                    'family' => [
                        'family_config',
                    ],
                    'name' => [
                        'text_config',
                        'label'      => 'Name',
                        'order'      => 2,
                        'group'      => 'General',
                        'groupOrder' => 1,
                    ],
                    'desc' => [
                        'text_config',
                        'label'      => 'Desc',
                        'order'      => 3,
                        'group'      => 'General',
                        'groupOrder' => 1,
                    ],
                ];
        $displayedColumns = $availableColumns;
        // we don't display the columns coming from the attributes by default
                array_pop($displayedColumns);
                array_pop($displayedColumns);

                $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
                $this->configuration->offsetSetByPath($columnConfPath, $displayedColumns)->shouldBeCalled();

                $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
                $this->configuration->offsetSetByPath($availableColumnPath, $availableColumns + $otherColumns)->shouldBeCalled();

                $this->configure($this->configuration);
            }

            // TODO: enable this test with TIP-664
            //    function it_displays_only_columns_configured_by_the_user($this->configuration)
            //    {
            //        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
            //
            //        $attributes = [
            //            'identifier' => [
            //                'code'          => 'sku',
            //                'label'         => 'Sku',
            //                'type'          => 'pim_catalog_identifier',
            //                'sortOrder'     => 1,
            //                'group'         => 'General',
            //                'groupOrder'    => 1
            //            ],
            //            'name' => [
            //                'code'          => 'name',
            //                'label'         => 'Name',
            //                'type'          => 'pim_catalog_text',
            //                'sortOrder'     => 2,
            //                'group'         => 'General',
            //                'groupOrder'    => 1
            //            ],
            //            'desc' => [
            //                'code'          => 'desc',
            //                'label'         => 'Desc',
            //                'type'          => 'pim_catalog_text',
            //                'sortOrder'     => 3,
            //                'group'         => 'General',
            //                'groupOrder'    => 1
            //            ],
            //        ];
            //        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
            //        $this->configuration->offsetGetByPath($path)->willReturn($attributes);
            //
            //        $userColumnsPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
            //        $this->configuration->offsetGetByPath($userColumnsPath)->willReturn(['family', 'sku']);
            //
            //        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);
            //        $this->configuration->offsetGetByPath($displayColumnPath)->shouldBeCalled();
            //
            //        $columns = [
            //            'identifier' => [
            //                'identifier_config',
            //                'label'      => 'Sku',
            //                'order'      => 1,
            //                'group'      => 'General',
            //                'groupOrder' => 1
            //            ],
            //            'family' => [
            //                'family_config',
            //            ],
            //        ];
            //        $this->configuration->offsetSetByPath($columnConfPath, $columns)->shouldBeCalled();
            //
            //        $columns = [
            //            'identifier' => [
            //                'identifier_config',
            //                'label'      => 'Sku',
            //                'order'      => 1,
            //                'group'      => 'General',
            //                'groupOrder' => 1
            //            ],
            //            'family' => [
            //                'family_config',
            //            ],
            //            'name' => [
            //                'text_config',
            //                'label'      => 'Name',
            //                'order'      => 2,
            //                'group'      => 'General',
            //                'groupOrder' => 1
            //            ],
            //            'desc' => [
            //                'text_config',
            //                'label'      => 'Desc',
            //                'order'      => 3,
            //                'group'      => 'General',
            //                'groupOrder' => 1
            //            ],
            //        ];
            //        $availableColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY);
            //        $this->configuration->offsetSetByPath($availableColumnPath, $columns)->shouldBeCalled();
            //
            //        $this->configure($this->configuration);
            //    }

            public function it_cannot_handle_misconfigured_attribute_type($this->configuration, $this->registry)
            {
                $this->registry->getConfiguration('pim_catalog_text')->willReturn([]);

                $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);

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
                $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
                $this->configuration->offsetGetByPath($path)->willReturn($attributes);

                $this->shouldThrow('\LogicException')->duringConfigure($this->configuration);
            }
        }
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        $this->registry->method('getConfiguration')->with('pim_catalog_text')->willReturn([]);
        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
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
        $path = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $this->configuration->method('offsetGetByPath')->with($path)->willReturn($attributes);
        $this->sut->shouldThrow('\LogicException')->duringConfigure($this->configuration);
    }
}
