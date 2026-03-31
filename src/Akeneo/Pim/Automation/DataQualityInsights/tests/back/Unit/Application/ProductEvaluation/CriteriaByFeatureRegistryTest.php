<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CriteriaByFeatureRegistryTest extends TestCase
{
    private FeatureFlag|MockObject $allCriteriaFeature;
    private CriterionCode $criterionCodeWithoutFeature;
    private CriterionCode $criterionCodeWhateverFeature;
    private CriterionCode $criterionCodeAllFeatureOnly;
    private CriteriaByFeatureRegistry $sut;

    protected function setUp(): void
    {
        $this->allCriteriaFeature = $this->createMock(FeatureFlag::class);
        $this->criterionCodeWithoutFeature = new CriterionCode('criterion_without_feature');
        $this->criterionCodeWhateverFeature = new CriterionCode('criterion_whatever_feature');
        $this->criterionCodeAllFeatureOnly = new CriterionCode('criterion_all_feature');

        $evaluateCriterionWithoutFeature = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCriterionWhateverFeature = $this->createMock(EvaluateCriterionInterface::class);
        $evaluateCriterionAllFeatureOnly = $this->createMock(EvaluateCriterionInterface::class);

        $evaluateCriterionWithoutFeature->method('getCode')->willReturn($this->criterionCodeWithoutFeature);
        $evaluateCriterionWhateverFeature->method('getCode')->willReturn($this->criterionCodeWhateverFeature);
        $evaluateCriterionAllFeatureOnly->method('getCode')->willReturn($this->criterionCodeAllFeatureOnly);

        $this->sut = new CriteriaByFeatureRegistry($this->allCriteriaFeature);
        $this->sut->register($evaluateCriterionWithoutFeature, null);
        $this->sut->register($evaluateCriterionWhateverFeature, 'whatever_feature');
        $this->sut->register($evaluateCriterionAllFeatureOnly, 'data_quality_insights_all_criteria');
    }

    public function test_it_gets_criteria_codes_with_all_criteria_feature_enabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(true);
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getEnabledCriterionCodes());
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getAllCriterionCodes());
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getPartialCriterionCodes());
    }

    public function test_it_gets_criteria_codes_with_all_criteria_feature_disabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(false);
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getEnabledCriterionCodes());
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getAllCriterionCodes());
        $this->assertEquals([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getPartialCriterionCodes());
    }
}
