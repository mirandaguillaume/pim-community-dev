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

        // Capture the exact offsetSetByPath calls
        $capturedCalls = [];
        $this->configuration->expects($this->exactly(2))
            ->method('offsetSetByPath')
            ->willReturnCallback(function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            });

        $this->sut->configure($this->configuration);

        // Verify displayed columns are set (properties only since no user columns)
        $displayedPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $this->assertArrayHasKey($displayedPath, $capturedCalls);
        $displayedColumns = $capturedCalls[$displayedPath];
        $this->assertArrayHasKey('family', $displayedColumns);

        // Verify available columns contain attribute columns and other columns
        $availablePath = sprintf('[source][%s]', ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $this->assertArrayHasKey($availablePath, $capturedCalls);
        $availableColumns = $capturedCalls[$availablePath];
        $this->assertArrayHasKey('name', $availableColumns);
        $this->assertArrayHasKey('desc', $availableColumns);
        $this->assertArrayHasKey('parent', $availableColumns);
        $this->assertArrayHasKey('family', $availableColumns);

        // Verify attribute columns have the correct label, order, group, groupOrder
        $this->assertSame('Name', $availableColumns['name']['label']);
        $this->assertSame(2, $availableColumns['name']['order']);
        $this->assertSame('General', $availableColumns['name']['group']);
        $this->assertSame(1, $availableColumns['name']['groupOrder']);

        // Verify attributes are sorted by label (Desc < Name)
        $attrKeys = array_keys(array_filter($availableColumns, fn ($k) => in_array($k, ['name', 'desc']), ARRAY_FILTER_USE_KEY));
        $this->assertSame('desc', $attrKeys[0]);
        $this->assertSame('name', $attrKeys[1]);
    }

    public function test_it_configures_with_editable_and_primary_columns(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturnMap([
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
        ];

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $useableAttrPath = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $otherColumnsPath = sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY);
        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);

        $this->configuration->method('offsetGetByPath')
            ->willReturnMap([
                [$columnConfPath, null, [
                    'family' => ['family_config'],
                    'editable_col' => ['editable' => true, 'some_config'],
                    'primary_col' => ['primary' => true, 'other_config'],
                ]],
                [$useableAttrPath, null, $attributes],
                [$otherColumnsPath, null, null],
                [$displayColumnPath, null, null],
            ]);

        $capturedCalls = [];
        $this->configuration->expects($this->exactly(2))
            ->method('offsetSetByPath')
            ->willReturnCallback(function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            });

        $this->sut->configure($this->configuration);

        $displayedPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $displayedColumns = $capturedCalls[$displayedPath];

        // Editable and primary columns should be in displayed columns
        $this->assertArrayHasKey('editable_col', $displayedColumns);
        $this->assertArrayHasKey('primary_col', $displayedColumns);
        $this->assertArrayHasKey('family', $displayedColumns);
        // Attributes are NOT in displayed (only in available) because no user columns
    }

    public function test_it_configures_with_user_columns(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturnMap([
                ['pim_catalog_text', ['column' => ['text_config']]],
            ]);

        $attributes = [
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'type'  => 'pim_catalog_text',
                'sortOrder' => 2,
                'group' => 'General',
                'groupOrder' => 1,
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
                [$otherColumnsPath, null, null],
                [$displayColumnPath, null, ['name', 'family']],
            ]);

        $capturedCalls = [];
        $this->configuration->expects($this->exactly(2))
            ->method('offsetSetByPath')
            ->willReturnCallback(function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            });

        $this->sut->configure($this->configuration);

        $displayedPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $displayedColumns = $capturedCalls[$displayedPath];

        // User selected columns should include name and family
        $this->assertArrayHasKey('name', $displayedColumns);
        $this->assertArrayHasKey('family', $displayedColumns);
    }

    public function test_it_handles_other_columns_as_null(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturnMap([
                ['pim_catalog_text', ['column' => ['text_config']]],
            ]);

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $useableAttrPath = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);
        $otherColumnsPath = sprintf('[%s]', Configuration::OTHER_COLUMNS_KEY);
        $displayColumnPath = sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::DISPLAYED_COLUMNS_KEY);

        $this->configuration->method('offsetGetByPath')
            ->willReturnMap([
                [$columnConfPath, null, ['family' => ['family_config']]],
                [$useableAttrPath, null, []],
                [$otherColumnsPath, null, null],
                [$displayColumnPath, null, null],
            ]);

        $capturedCalls = [];
        $this->configuration->method('offsetSetByPath')
            ->willReturnCallback(function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            });

        $this->sut->configure($this->configuration);

        $availablePath = sprintf('[source][%s]', ContextConfigurator::AVAILABLE_COLUMNS_KEY);
        $availableColumns = $capturedCalls[$availablePath];
        // With no other columns (null), only family remains
        $this->assertArrayHasKey('family', $availableColumns);
        $this->assertCount(1, $availableColumns);
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

    public function test_it_throws_when_configuration_returns_null(): void
    {
        $this->registry->method('getConfiguration')
            ->willReturn(null);

        $attributes = [
            'name' => [
                'code'  => 'name',
                'label' => 'Name',
                'type'  => 'pim_catalog_text',
                'sortOrder' => 2,
                'group' => 'General',
                'groupOrder' => 1,
            ],
        ];

        $columnConfPath = sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY);
        $useableAttrPath = sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY);

        $this->configuration->method('offsetGetByPath')
            ->willReturnMap([
                [$columnConfPath, null, []],
                [$useableAttrPath, null, $attributes],
            ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Attribute type pim_catalog_text must be configured to display attribute name as grid column');
        $this->sut->configure($this->configuration);
    }
}
