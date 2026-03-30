<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\GetScoresByCriteriaStrategy;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\GetQualityScoresFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetQualityScoresFactoryTest extends TestCase
{
    private GetProductScoresQueryInterface|MockObject $getProductScoresQuery;
    private GetProductModelScoresQueryInterface|MockObject $getProductModelScoresQuery;
    private GetScoresByCriteriaStrategy|MockObject $getScoresByCriteria;
    private GetQualityScoresFactory $sut;

    protected function setUp(): void
    {
        $this->getProductScoresQuery = $this->createMock(GetProductScoresQueryInterface::class);
        $this->getProductModelScoresQuery = $this->createMock(GetProductModelScoresQueryInterface::class);
        $this->getScoresByCriteria = $this->createMock(GetScoresByCriteriaStrategy::class);
        $this->sut = new GetQualityScoresFactory($this->getProductScoresQuery, $this->getProductModelScoresQuery, $this->getScoresByCriteria);
    }

    public function test_it_gets_quality_scores_for_products(): void
    {
        $productUuids = ProductUuidCollection::fromStrings([
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2',
                    '3370280b-6c76-4720-aac1-ae3f9613d555',
                ]);
        $scores = $this->givenProductScores();
        $this->getProductScoresQuery->method('byProductUuidCollection')->with($productUuids)->willReturn($scores);
        $this->getScoresByCriteria->method('__invoke')->with($scores['0932dfd0-5f9a-49fb-ad31-a990339406a2'])->willReturn($scores['0932dfd0-5f9a-49fb-ad31-a990339406a2']->allCriteria());
        $this->getScoresByCriteria->method('__invoke')->with($scores['3370280b-6c76-4720-aac1-ae3f9613d555'])->willReturn($scores['3370280b-6c76-4720-aac1-ae3f9613d555']->allCriteria());
        $this->assertSame([
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2' => $scores['0932dfd0-5f9a-49fb-ad31-a990339406a2']->allCriteria(),
                    '3370280b-6c76-4720-aac1-ae3f9613d555' => $scores['3370280b-6c76-4720-aac1-ae3f9613d555']->allCriteria(),
                ], $this->sut->__invoke($productUuids, 'product'));
    }

    public function test_it_gets_quality_scores_for_product_models(): void
    {
        $productModelIds = ProductModelIdCollection::fromStrings(['42', '56']);
        $scores = $this->givenProductModelScores();
        $this->getProductModelScoresQuery->method('byProductModelIdCollection')->with($productModelIds)->willReturn($scores);
        $this->getScoresByCriteria->method('__invoke')->with($scores[42])->willReturn($scores[42]->allCriteria());
        $this->getScoresByCriteria->method('__invoke')->with($scores[56])->willReturn($scores[56]->allCriteria());
        $this->assertSame([
                    42 => $scores[42]->allCriteria(),
                    56 => $scores[56]->allCriteria(),
                ], $this->sut->__invoke($productModelIds, 'product_model'));
    }

    public function test_it_throws_an_exception_for_an_unknown_type(): void
    {
        $productUuids = ProductUuidCollection::fromStrings([
                    '0932dfd0-5f9a-49fb-ad31-a990339406a2',
                    '3370280b-6c76-4720-aac1-ae3f9613d555',
                ]);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke($productUuids, 'whatever');
    }

    private function givenProductScores(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
    
        return [
            '0932dfd0-5f9a-49fb-ad31-a990339406a2' => new Read\Scores(
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(76)),
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(65))
            ),
            '3370280b-6c76-4720-aac1-ae3f9613d555' => new Read\Scores(
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(98)),
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(84))
            ),
        ];
    }

    private function givenProductModelScores(): array
    {
        $channel = new ChannelCode('ecommerce');
        $locale = new LocaleCode('en_US');
    
        return [
            42 => new Read\Scores(
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(76)),
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(65))
            ),
            56 => new Read\Scores(
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(98)),
                (new ChannelLocaleRateCollection())->addRate($channel, $locale, new Rate(84))
            ),
        ];
    }
}
