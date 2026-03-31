<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\KeyIndicator\ProductKeyIndicatorsByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\ComputeProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\KeyIndicatorCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductKeyIndicatorsByFeatureRegistryTest extends TestCase
{
    private FeatureFlag|MockObject $allCriteriaFeature;
    private ComputeProductsKeyIndicator|MockObject $keyIndicatorA;
    private ComputeProductsKeyIndicator|MockObject $keyIndicatorB;
    private ComputeProductsKeyIndicator|MockObject $allCriteriaOnlyKeyIndicatorB;
    private ProductKeyIndicatorsByFeatureRegistry $sut;

    protected function setUp(): void
    {
        $this->allCriteriaFeature = $this->createMock(FeatureFlag::class);
        $this->keyIndicatorA = $this->createMock(ComputeProductsKeyIndicator::class);
        $this->keyIndicatorB = $this->createMock(ComputeProductsKeyIndicator::class);
        $this->allCriteriaOnlyKeyIndicatorB = $this->createMock(ComputeProductsKeyIndicator::class);
        $this->sut = new ProductKeyIndicatorsByFeatureRegistry($this->allCriteriaFeature);
        $this->keyIndicatorA->method('getCode')->willReturn(new KeyIndicatorCode('ki_A'));
        $this->keyIndicatorB->method('getCode')->willReturn(new KeyIndicatorCode('ki_B'));
        $this->allCriteriaOnlyKeyIndicatorB->method('getCode')->willReturn(new KeyIndicatorCode('all_criteria_only_ki'));
        $this->sut->register($this->keyIndicatorA, null);
        $this->sut->register($this->keyIndicatorB, 'whatever_feature');
        $this->sut->register($this->allCriteriaOnlyKeyIndicatorB, 'data_quality_insights_all_criteria');
    }

    public function test_it_gets_all_key_indicators_when_all_criteria_feature_is_enabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(true);
        $this->assertEquals([
                    new KeyIndicatorCode('ki_A'),
                    new KeyIndicatorCode('ki_B'),
                    new KeyIndicatorCode('all_criteria_only_ki'),
                ], $this->sut->getCodes());
    }

    public function test_it_gets_partial_key_indicators_when_all_criteria_feature_is_disabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(false);
        $this->assertEquals([
                    new KeyIndicatorCode('ki_A'),
                    new KeyIndicatorCode('ki_B'),
                ], $this->sut->getCodes());
    }
}
