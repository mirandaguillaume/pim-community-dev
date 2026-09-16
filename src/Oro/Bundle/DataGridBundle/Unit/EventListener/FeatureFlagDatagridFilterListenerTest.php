<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\DataGridBundle\EventListener;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\EventListener\FeatureFlagDatagridFilterListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Guards the build.before listener of product-grid and attribute-grid: a column, filter or sorter carrying a
 * feature_flag must disappear when that flag is disabled, and everything else must be left untouched and in order.
 * This is what hides the "Quality score" column, filters and sorter when data_quality_insights is off.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureFlagDatagridFilterListenerTest extends TestCase
{
    private FeatureFlags|MockObject $featureFlags;
    private FeatureFlagDatagridFilterListener $sut;

    protected function setUp(): void
    {
        $this->featureFlags = $this->createMock(FeatureFlags::class);
        $this->sut = new FeatureFlagDatagridFilterListener($this->featureFlags);
    }

    public function test_it_removes_the_columns_filters_and_sorters_of_a_disabled_feature(): void
    {
        $this->givenTheFeatureFlags(['data_quality_insights' => false, 'enabled_feature' => true]);
        $config = $this->productGridConfiguration();

        $this->sut->filterColumns($this->buildBeforeEvent($config));

        $this->assertSame(
            ['identifier', 'enabled_feature_column', 'updated'],
            array_keys($config->offsetGet('columns'))
        );
        $this->assertSame(['label' => 'ID'], $config->offsetGet('columns')['identifier']);
        $this->assertSame(
            ['family', 'enabled_feature_filter', 'enabled'],
            array_keys($config->offsetGetByPath('[filters][columns]'))
        );
        $this->assertSame(
            ['enabled' => ['value' => 1]],
            $config->offsetGetByPath('[filters][default]'),
            'the other filters settings must be kept'
        );
        $this->assertSame(
            ['identifier', 'updated'],
            array_keys($config->offsetGetByPath('[sorters][columns]'))
        );
        $this->assertSame(
            ['updated' => 'DESC'],
            $config->offsetGetByPath('[sorters][default]'),
            'the other sorters settings must be kept'
        );
    }

    public function test_it_keeps_the_columns_filters_and_sorters_of_an_enabled_feature(): void
    {
        $this->givenTheFeatureFlags(['data_quality_insights' => true, 'enabled_feature' => true]);
        $expected = $this->productGridConfiguration();
        $config = $this->productGridConfiguration();

        $this->sut->filterColumns($this->buildBeforeEvent($config));

        $this->assertSame($expected->offsetGet('columns'), $config->offsetGet('columns'));
        $this->assertSame($expected->offsetGet('filters'), $config->offsetGet('filters'));
        $this->assertSame($expected->offsetGet('sorters'), $config->offsetGet('sorters'));
    }

    public function test_it_does_not_check_any_flag_when_nothing_is_flagged(): void
    {
        $this->featureFlags->expects($this->never())->method('isEnabled');
        $config = DatagridConfiguration::create([
            'name' => 'attribute-grid',
            'columns' => ['code' => ['label' => 'Code'], 'label' => ['label' => 'Label']],
            'filters' => ['columns' => ['code' => ['type' => 'string']]],
            'sorters' => ['columns' => ['code' => ['data_name' => 'a.code']]],
        ]);

        $this->sut->filterColumns($this->buildBeforeEvent($config));

        $this->assertSame(['code' => ['label' => 'Code'], 'label' => ['label' => 'Label']], $config->offsetGet('columns'));
        $this->assertSame(['columns' => ['code' => ['type' => 'string']]], $config->offsetGet('filters'));
        $this->assertSame(['code' => ['data_name' => 'a.code']], $config->offsetGetByPath('[sorters][columns]'));
    }

    public function test_it_leaves_a_grid_without_filter_columns_nor_sorters_untouched(): void
    {
        $this->givenTheFeatureFlags(['data_quality_insights' => false]);
        $config = DatagridConfiguration::create([
            'name' => 'a-grid',
            'columns' => [
                'code' => ['label' => 'Code'],
                'score' => ['label' => 'Score', 'feature_flag' => 'data_quality_insights'],
            ],
            'filters' => [],
        ]);

        $this->sut->filterColumns($this->buildBeforeEvent($config));

        $this->assertSame(['code' => ['label' => 'Code']], $config->offsetGet('columns'));
        $this->assertSame([], $config->offsetGet('filters'));
        $this->assertNull($config->offsetGetByPath('[sorters][columns]'));
    }

    /**
     * @param array<string, bool> $flags
     */
    private function givenTheFeatureFlags(array $flags): void
    {
        $this->featureFlags->method('isEnabled')->willReturnCallback(
            static function (string $feature) use ($flags): bool {
                if (!array_key_exists($feature, $flags)) {
                    throw new \InvalidArgumentException(sprintf('Unexpected feature flag "%s"', $feature));
                }

                return $flags[$feature];
            }
        );
    }

    private function buildBeforeEvent(DatagridConfiguration $config): BuildBefore
    {
        return new BuildBefore($this->createMock(DatagridInterface::class), $config);
    }

    private function productGridConfiguration(): DatagridConfiguration
    {
        return DatagridConfiguration::create([
            'name' => 'product-grid',
            'columns' => [
                'identifier' => ['label' => 'ID'],
                'data_quality_insights_score' => [
                    'label' => 'Quality score',
                    'feature_flag' => 'data_quality_insights',
                ],
                'enabled_feature_column' => ['label' => 'Enabled feature', 'feature_flag' => 'enabled_feature'],
                'updated' => ['label' => 'Updated'],
            ],
            'filters' => [
                'columns' => [
                    'family' => ['type' => 'product_family'],
                    'data_quality_insights_score' => [
                        'type' => 'data_quality_insights_score',
                        'feature_flag' => 'data_quality_insights',
                    ],
                    'enabled_feature_filter' => ['type' => 'choice', 'feature_flag' => 'enabled_feature'],
                    'data_quality_insights_images_quality' => [
                        'type' => 'data_quality_insights_images_quality',
                        'feature_flag' => 'data_quality_insights',
                    ],
                    'enabled' => ['type' => 'product_enabled'],
                ],
                'default' => ['enabled' => ['value' => 1]],
            ],
            'sorters' => [
                'columns' => [
                    'identifier' => ['data_name' => 'identifier'],
                    'data_quality_insights_score' => [
                        'data_name' => 'quality_score',
                        'feature_flag' => 'data_quality_insights',
                    ],
                    'updated' => ['data_name' => 'updated'],
                ],
                'default' => ['updated' => 'DESC'],
            ],
        ]);
    }
}
