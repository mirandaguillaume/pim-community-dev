<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductModelsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQueryHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListProductModelsQueryHandlerTest extends TestCase
{
    private ListProductModelsQueryHandler $sut;

    protected function setUp(): void
    {
        $this->sut = new ListProductModelsQueryHandler();
    }

    public function test_it_add_quality_scores_to_products_if_option_is_activated(): void
    {
        $fromSizePqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $searchAfterPqbFactory = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $pqb = $this->createMock(ProductQueryBuilderInterface::class);
        $getConnectorProductModels = $this->createMock(GetConnectorProductModels::class);
        $getProductModelsWithQualityScores = $this->createMock(GetProductModelsWithQualityScoresInterface::class);

        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::OFFSET;
        $query->limit = 42;
        $query->page = 69;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US'];
        $query->attributeCodes = ['name'];
        $query->userId = 42;
        $query->withQualityScores = 'true';
        $fromSizePqbFactory->expects($this->once())->method('create')->with([
                    'limit' => 42,
                    'from' => 2856
                ])->willReturn($pqb);
        $pqb->expects($this->once())->method('addSorter')->with('identifier', Directions::ASCENDING);
        $connectorProductModel1 = new ConnectorProductModel(
                    1234,
                    'code_1',
                    new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                    new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                    'my_parent',
                    'my_family',
                    'my_family_variant',
                    ['workflow_status' => 'working_copy'],
                    [],
                    [],
                    ['category_code_1'],
                    new ReadValueCollection(),
                    null
                );
        $connectorProductModelList = new ConnectorProductModelList(1, [$connectorProductModel1]);
        $getConnectorProductModels->method('fromProductQueryBuilder')->with($pqb, 42, ['name'], 'tablet', ['en_US'])->willReturn(new ConnectorProductModelList(1, [$connectorProductModel1]));
        $connectorProductModelListWithQualityScores = new ConnectorProductModelList(1, [
                    $connectorProductModel1->buildWithQualityScores(new QualityScoreCollection([
                        'tablet' => [
                            'en_US' => new QualityScore('C', 76),
                        ]
                    ]))
                ]);
        $getProductModelsWithQualityScores->method('fromConnectorProductModelList')->with($connectorProductModelList, 'tablet', ['en_US'])->willReturn($connectorProductModelListWithQualityScores);
        $searchAfterPqbFactory->expects($this->never())->method('create');
        $this->assertEquals($connectorProductModelListWithQualityScores, $this->sut->handle($query));
    }
}
