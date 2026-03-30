<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Elasticsearch;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\GetScoresPropertyStrategy;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetScoresPropertyStrategyTest extends TestCase
{
    private FeatureFlag|MockObject $allCriteriaFeature;
    private GetScoresPropertyStrategy $sut;

    protected function setUp(): void
    {
        $this->allCriteriaFeature = $this->createMock(FeatureFlag::class);
        $this->sut = new GetScoresPropertyStrategy($this->allCriteriaFeature);
    }

    public function test_it_gets_the_scores_property_when_the_feature_all_criteria_is_enabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(true);
        $this->assertSame('scores', $this->sut->__invoke());
    }

    public function test_it_gets_the_scores_property_when_the_feature_all_criteria_is_disabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(false);
        $this->assertSame('scores_partial_criteria', $this->sut->__invoke());
    }
}
