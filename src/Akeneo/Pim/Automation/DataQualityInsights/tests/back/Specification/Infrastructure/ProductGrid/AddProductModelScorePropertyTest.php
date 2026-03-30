<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\AddProductModelScoreProperty;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\AddScoresToProductAndProductModelRows;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddProductModelScorePropertyTest extends TestCase
{
    private AddScoresToProductAndProductModelRows|MockObject $addScoresToProductAndProductModelRows;
    private AddProductModelScoreProperty $sut;

    protected function setUp(): void
    {
        $this->addScoresToProductAndProductModelRows = $this->createMock(AddScoresToProductAndProductModelRows::class);
        $this->sut = new AddProductModelScoreProperty($this->addScoresToProductAndProductModelRows);
    }

    public function test_it_returns_row_with_additional_property_DQI_score(): void
    {
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);

        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder,
            [],
            'ecommerce',
            'en_US'
        );
        $rows = [$this->makeProductModelRow('1'), $this->makeProductModelRow('4')];
        $this->addScoresToProductAndProductModelRows->expects($this->once())->method('__invoke')->with($queryParameters, $rows, 'product_model');
        $this->sut->add($queryParameters, $rows)->shouldHaveScoreProperties();
    }

    private function makeProductModelRow(string $technicalId): Row
    {
        return Row::fromProduct(
            sprintf('product_model_%s', $technicalId), // identifier
            null, // family
            [], // groupCodes
            true, // $enabled,
            new \DateTime(), // created
            new \DateTime(), // updated
            sprintf('Label of %s', $technicalId), // label
            null, // image
            null, // completeness,
            $technicalId, //technicalId,
            null, // parentCode,
            new WriteValueCollection() // values,
        );
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
