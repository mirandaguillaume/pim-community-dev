<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpToDateProductScoresQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class GetUpToDateProductScoresQueryTest extends TestCase
{
    private HasUpToDateEvaluationQueryInterface|MockObject $hasUpToDateEvaluationQuery;
    private GetProductScoresQueryInterface|MockObject $getProductScoresQuery;
    private GetUpToDateProductScoresQuery $sut;

    protected function setUp(): void
    {
        $this->hasUpToDateEvaluationQuery = $this->createMock(HasUpToDateEvaluationQueryInterface::class);
        $this->getProductScoresQuery = $this->createMock(GetProductScoresQueryInterface::class);
        $this->sut = new GetUpToDateProductScoresQuery($this->hasUpToDateEvaluationQuery, $this->getProductScoresQuery);
    }

    public function test_it_returns_the_product_scores_if_the_evaluation_of_the_product_is_up_to_date(): void
    {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $productScores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(78))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(67))
        );
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(true);
        $this->getProductScoresQuery->method('byProductUuid')->with($productUuid)->willReturn($productScores);
        $this->assertSame($productScores, $this->sut->byProductUuid($productUuid));
    }

    public function test_it_returns_empty_scores_if_the_evaluation_of_the_product_is_outdated(): void
    {
        $productUuid = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productUuid)->willReturn(false);
        $this->getProductScoresQuery->expects($this->never())->method('byProductUuid')->with($productUuid);
        $this->assertEquals(new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection()), $this->sut->byProductUuid($productUuid));
    }

    public function test_it_returns_the_product_scores_only_for_up_to_date_products(): void
    {
        $productUuidA = ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed');
        $productUuidB = ProductUuid::fromString('fef37e64-a963-47a9-b087-2cc67968f0a2');
        $productUuidC = ProductUuid::fromString('6d125b99-d971-41d9-a264-b020cd486aee');
        $productUuidCollection = ProductUuidCollection::fromProductUuids([$productUuidA, $productUuidB, $productUuidC]);
        $upToDateProductUuidCollection = ProductUuidCollection::fromProductUuids([$productUuidA, $productUuidB]);
        $productsScores = [
                    42 => new Read\Scores(
                        (new ChannelLocaleRateCollection())
                            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100)),
                        (new ChannelLocaleRateCollection())
                            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(90)),
                    ),
                    123 => new Read\Scores(
                        (new ChannelLocaleRateCollection())
                            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(45)),
                        (new ChannelLocaleRateCollection())
                            ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(67)),
                    ),
                ];
        $this->hasUpToDateEvaluationQuery->method('forEntityIdCollection')->with($productUuidCollection)->willReturn($upToDateProductUuidCollection);
        $this->getProductScoresQuery->method('byProductUuidCollection')->with($upToDateProductUuidCollection)->willReturn($productsScores);
        $this->assertSame($productsScores, $this->sut->byProductUuidCollection($productUuidCollection));
    }

    public function test_it_returns_empty_array_if_there_are_no_up_to_date_products(): void
    {
        $products = ProductUuidCollection::fromStrings([
                    'df470d52-7723-4890-85a0-e79be625e2ed',
                    'fef37e64-a963-47a9-b087-2cc67968f0a2',
                ]);
        $this->hasUpToDateEvaluationQuery->method('forEntityIdCollection')->with($products)->willReturn(null);
        $this->getProductScoresQuery->expects($this->never())->method('byProductUuidCollection')->with($this->anything());
        $this->assertSame([], $this->sut->byProductUuidCollection($products));
    }
}
