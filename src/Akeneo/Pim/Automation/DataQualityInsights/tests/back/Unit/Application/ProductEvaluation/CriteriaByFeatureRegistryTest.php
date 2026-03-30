<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriterionInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\FeatureFlag\AllCriteriaFeature;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CriteriaByFeatureRegistryTest extends TestCase
{
    private FeatureFlag|MockObject $allCriteriaFeature;
    private EvaluateCriterionInterface|MockObject $evaluateCriterionWithoutFeature;
    private EvaluateCriterionInterface|MockObject $evaluateCriterionWhateverFeature;
    private EvaluateCriterionInterface|MockObject $evaluateCriterionAllFeatureOnly;
    private CriteriaByFeatureRegistry $sut;

    protected function setUp(): void
    {
        $this->allCriteriaFeature = $this->createMock(FeatureFlag::class);
        $this->evaluateCriterionWithoutFeature = $this->createMock(EvaluateCriterionInterface::class);
        $this->evaluateCriterionWhateverFeature = $this->createMock(EvaluateCriterionInterface::class);
        $this->evaluateCriterionAllFeatureOnly = $this->createMock(EvaluateCriterionInterface::class);
        $this->sut = new CriteriaByFeatureRegistry($this->allCriteriaFeature);
        $this->sut->criterionCodeWithoutFeature = new CriterionCode('criterion_without_feature');
        $this->sut->criterionCodeWhateverFeature = new CriterionCode('criterion_whatever_feature');
        $this->sut->criterionCodeAllFeatureOnly = new CriterionCode('criterion_all_feature');
        $this->evaluateCriterionWithoutFeature->method('getCode')->willReturn($this->criterionCodeWithoutFeature);
        $this->evaluateCriterionWhateverFeature->method('getCode')->willReturn($this->criterionCodeWhateverFeature);
        $this->evaluateCriterionAllFeatureOnly->method('getCode')->willReturn($this->criterionCodeAllFeatureOnly);
        $this->sut->register($this->evaluateCriterionWithoutFeature, null);
        $this->sut->register($this->evaluateCriterionWhateverFeature, 'whatever_feature');
        $this->sut->register($this->evaluateCriterionAllFeatureOnly, 'data_quality_insights_all_criteria');
    }

    public function test_it_gets_criteria_codes_with_all_criteria_feature_enabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(true);
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getEnabledCriterionCodes());
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getAllCriterionCodes());
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getPartialCriterionCodes());
    }

    public function test_it_gets_criteria_codes_with_all_criteria_feature_disabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(false);
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getEnabledCriterionCodes());
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature, $this->criterionCodeAllFeatureOnly], $this->sut->getAllCriterionCodes());
        $this->assertSame([$this->criterionCodeWithoutFeature, $this->criterionCodeWhateverFeature], $this->sut->getPartialCriterionCodes());
    }
}
