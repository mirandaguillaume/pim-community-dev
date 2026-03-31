<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration as OrmSorterConfiguration;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\ConfiguratorInterface;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ConfigurationRegistry;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\ContextConfigurator;
use Oro\Bundle\PimDataGridBundle\Datagrid\Configuration\Product\SortersConfigurator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SortersConfiguratorTest extends TestCase
{
    private ConfigurationRegistry|MockObject $registry;
    private SortersConfigurator $sut;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ConfigurationRegistry::class);
        $this->sut = new SortersConfigurator($this->registry);
    }

    public function test_it_is_a_configurator(): void
    {
        $this->assertInstanceOf(ConfiguratorInterface::class, $this->sut);
    }

    public function test_it_configures_datagrid_sorters(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);
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

        $this->registry->method('getConfiguration')->willReturnCallback(function (string $type) {
            return match ($type) {
                'pim_catalog_identifier' => ['column' => ['identifier_config'], 'sorter' => 'flexible_field'],
                'pim_catalog_text' => ['column' => ['text_config'], 'sorter' => 'flexible_field'],
                default => [],
            };
        });

        $existingSorters = ['family' => ['data_name' => 'family']];

        $configuration->method('offsetGetByPath')->willReturnCallback(
            function (string $path) use ($attributes, $existingSorters) {
                if ($path === sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY)) {
                    return $attributes;
                }
                if ($path === sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)) {
                    return ['family' => ['family_config'], 'identifier' => [], 'name' => []];
                }
                if ($path === OrmSorterConfiguration::COLUMNS_PATH) {
                    return $existingSorters;
                }
                return null;
            }
        );

        $capturedCalls = [];
        $configuration->method('offsetSetByPath')->willReturnCallback(
            function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            }
        );

        $this->sut->configure($configuration);

        // Verify identifier sorter was added (sku mapped to 'identifier')
        $identifierSorterPath = sprintf('%s[identifier]', OrmSorterConfiguration::COLUMNS_PATH);
        $this->assertArrayHasKey($identifierSorterPath, $capturedCalls);
        $this->assertSame('identifier', $capturedCalls[$identifierSorterPath][PropertyInterface::DATA_NAME_KEY]);
        $this->assertSame('flexible_field', $capturedCalls[$identifierSorterPath]['sorter']);

        // Verify name sorter was added
        $nameSorterPath = sprintf('%s[name]', OrmSorterConfiguration::COLUMNS_PATH);
        $this->assertArrayHasKey($nameSorterPath, $capturedCalls);
        $this->assertSame('name', $capturedCalls[$nameSorterPath][PropertyInterface::DATA_NAME_KEY]);
        $this->assertSame('flexible_field', $capturedCalls[$nameSorterPath]['sorter']);

        // Verify removeExtraSorters was called
        $cleanedSortersPath = OrmSorterConfiguration::COLUMNS_PATH;
        $this->assertArrayHasKey($cleanedSortersPath, $capturedCalls);
        $cleanedSorters = $capturedCalls[$cleanedSortersPath];
        $this->assertArrayHasKey('family', $cleanedSorters);
    }

    public function test_it_removes_sorters_for_non_displayed_columns(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $this->registry->method('getConfiguration')->willReturn(null);

        $configuration->method('offsetGetByPath')->willReturnCallback(
            function (string $path) {
                if ($path === sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY)) {
                    return [];
                }
                if ($path === sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)) {
                    return ['family' => ['config']];
                }
                if ($path === OrmSorterConfiguration::COLUMNS_PATH) {
                    return ['family' => ['sorter_config'], 'removed_col' => ['sorter_config']];
                }
                return null;
            }
        );

        $capturedCalls = [];
        $configuration->method('offsetSetByPath')->willReturnCallback(
            function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            }
        );

        $this->sut->configure($configuration);

        $cleanedSorters = $capturedCalls[OrmSorterConfiguration::COLUMNS_PATH];
        $this->assertArrayHasKey('family', $cleanedSorters);
        $this->assertArrayNotHasKey('removed_col', $cleanedSorters);
    }

    public function test_it_does_not_add_sorter_when_column_is_not_displayed(): void
    {
        $configuration = $this->createMock(DatagridConfiguration::class);

        $attributes = [
            'name' => [
                'code' => 'name',
                'type' => 'pim_catalog_text',
            ],
        ];

        $this->registry->method('getConfiguration')->willReturn([
            'column' => ['text_config'],
            'sorter' => 'flexible_field',
        ]);

        $configuration->method('offsetGetByPath')->willReturnCallback(
            function (string $path) use ($attributes) {
                if ($path === sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY)) {
                    return $attributes;
                }
                if ($path === sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY)) {
                    // name is NOT in the displayed columns
                    return ['family' => ['family_config']];
                }
                if ($path === OrmSorterConfiguration::COLUMNS_PATH) {
                    return null;
                }
                return null;
            }
        );

        $capturedCalls = [];
        $configuration->method('offsetSetByPath')->willReturnCallback(
            function (string $path, $value) use (&$capturedCalls) {
                $capturedCalls[$path] = $value;
            }
        );

        $this->sut->configure($configuration);

        // Name sorter should NOT be added since column is not displayed
        $nameSorterPath = sprintf('%s[name]', OrmSorterConfiguration::COLUMNS_PATH);
        $this->assertArrayNotHasKey($nameSorterPath, $capturedCalls);
    }

    public function test_it_cannot_handle_misconfigured_attribute_type(): void
    {
        $this->registry->method('getConfiguration')->willReturnCallback(function (string $type) {
            return match ($type) {
                'pim_catalog_identifier' => ['column' => ['identifier_config'], 'sorter' => 'flexible_field'],
                default => [],
            };
        });

        $attributes = [
            'sku' => ['code' => 'sku', 'type' => 'pim_catalog_identifier'],
            'name' => ['code' => 'name', 'type' => 'pim_catalog_text'],
        ];
        $configuration = $this->createMock(DatagridConfiguration::class);
        $configuration->method('offsetGetByPath')->willReturnCallback(function (string $path) use ($attributes) {
            return match ($path) {
                sprintf('[source][%s]', ContextConfigurator::USEABLE_ATTRIBUTES_KEY) => $attributes,
                sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY) => ['family' => ['family_config'], 'identifier' => [], 'name' => []],
                default => null,
            };
        });
        $configuration->method('offsetSetByPath');
        $this->expectException(\LogicException::class);
        $this->sut->configure($configuration);
    }
}
