<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpToDateProductModelScoresQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetProductModelScoresTest extends TestCase
{
    private GetUpToDateProductModelScoresQuery|MockObject $getUpToDateProductModelScoresQuery;
    private GetLocalesByChannelQueryInterface|MockObject $getLocalesByChannelQuery;
    private GetScoresByCriteriaStrategy|MockObject $getScoresByCriteria;
    private GetProductModelScores $sut;

    protected function setUp(): void
    {
        $this->getUpToDateProductModelScoresQuery = $this->createMock(GetUpToDateProductModelScoresQuery::class);
        $this->getLocalesByChannelQuery = $this->createMock(GetLocalesByChannelQueryInterface::class);
        $this->getScoresByCriteria = $this->createMock(GetScoresByCriteriaStrategy::class);
        $this->sut = new GetProductModelScores($this->getUpToDateProductModelScoresQuery, $this->getLocalesByChannelQuery, $this->getScoresByCriteria);
    }

    public function test_it_gives_the_scores_by_channel_and_locale_for_a_given_product_model(): void
    {
        $productModelId = new ProductModelId(42);
        $this->getLocalesByChannelQuery->method('getChannelLocaleCollection')->willReturn(new ChannelLocaleCollection([
                    'ecommerce' => ['en_US', 'fr_FR'],
                    'mobile' => ['en_US'],
                ]));
        $scores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                        ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90))
                        ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(70))
        );
        $this->getUpToDateProductModelScoresQuery->method('byProductModelId')->with($productModelId)->willReturn($scores);
        $this->getScoresByCriteria->method('__invoke')->with($scores)->willReturn($scores->allCriteria());
        $this->assertEquals([
                        "evaluations_available" => true,
                        "scores" => [
                            'ecommerce' => [
                                'en_US' => (new Rate(100))->toLetter(),
                                'fr_FR' => null,
                            ],
                            'mobile' => [
                                'en_US' => (new Rate(80))->toLetter(),
                            ],
                        ],
                    ], $this->sut->get($productModelId));
    }
}
