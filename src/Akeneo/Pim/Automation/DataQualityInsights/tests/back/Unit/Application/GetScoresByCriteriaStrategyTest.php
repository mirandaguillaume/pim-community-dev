<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetScoresByCriteriaStrategyTest extends TestCase
{
    private FeatureFlag|MockObject $allCriteriaFeature;
    private GetScoresByCriteriaStrategy $sut;

    protected function setUp(): void
    {
        $this->allCriteriaFeature = $this->createMock(FeatureFlag::class);
        $this->sut = new GetScoresByCriteriaStrategy($this->allCriteriaFeature);
    }

    public function test_it_gets_scores_all_criteria_when_the_feature_dqi_all_criteria_is_enabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(true);
        $scores = $this->givenScores();
        $this->assertSame($scores->allCriteria(), $this->sut->__invoke($scores));
    }

    public function test_it_gets_scores_partial_criteria_when_the_feature_dqi_all_criteria_is_disabled(): void
    {
        $this->allCriteriaFeature->method('isEnabled')->willReturn(false);
        $scores = $this->givenScores();
        $this->assertSame($scores->partialCriteria(), $this->sut->__invoke($scores));
    }

    private function givenScores(): Read\Scores
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
    
        return new Read\Scores(
            (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(76)),
            (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(65))
        );
    }
}
