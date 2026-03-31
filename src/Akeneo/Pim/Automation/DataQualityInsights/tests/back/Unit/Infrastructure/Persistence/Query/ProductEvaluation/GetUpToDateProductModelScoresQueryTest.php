<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductModelScoresQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\HasUpToDateEvaluationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetUpToDateProductModelScoresQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetUpToDateProductModelScoresQueryTest extends TestCase
{
    private HasUpToDateEvaluationQueryInterface|MockObject $hasUpToDateEvaluationQuery;
    private GetProductModelScoresQueryInterface|MockObject $getProductModelScoresQuery;
    private GetUpToDateProductModelScoresQuery $sut;

    protected function setUp(): void
    {
        $this->hasUpToDateEvaluationQuery = $this->createMock(HasUpToDateEvaluationQueryInterface::class);
        $this->getProductModelScoresQuery = $this->createMock(GetProductModelScoresQueryInterface::class);
        $this->sut = new GetUpToDateProductModelScoresQuery($this->hasUpToDateEvaluationQuery, $this->getProductModelScoresQuery);
    }

    public function test_it_returns_the_product_model_scores_if_evaluation_for_product_id_is_up_to_date(): void
    {
        $productModelId = new ProductModelId(42);
        $scores = new Read\Scores(
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(100))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(80)),
            (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(98))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(74))
        );
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productModelId)->willReturn(true);
        $this->getProductModelScoresQuery->method('byProductModelId')->with($productModelId)->willReturn($scores);
        $this->assertSame($scores, $this->sut->byProductModelId($productModelId));
    }

    public function test_it_returns_empty_scores_if_evaluation_for_product_id_is_outdated(): void
    {
        $productModelId = new ProductModelId(42);
        $this->hasUpToDateEvaluationQuery->method('forEntityId')->with($productModelId)->willReturn(false);
        $this->getProductModelScoresQuery->expects($this->never())->method('byProductModelId')->with($productModelId);
        $this->assertEquals(new Read\Scores(new ChannelLocaleRateCollection(), new ChannelLocaleRateCollection()), $this->sut->byProductModelId($productModelId));
    }
}
