<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\AddScoresToProductAndProductModelRows;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid\GetQualityScoresFactory;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddScoresToProductAndProductModelRowsTest extends TestCase
{
    private GetQualityScoresFactory|MockObject $getQualityScoresFactory;
    private ProductEntityIdFactoryInterface|MockObject $idFactory;
    private AddScoresToProductAndProductModelRows $sut;

    protected function setUp(): void
    {
        $this->getQualityScoresFactory = $this->createMock(GetQualityScoresFactory::class);
        $this->idFactory = $this->createMock(ProductEntityIdFactoryInterface::class);
        $this->sut = new AddScoresToProductAndProductModelRows($this->getQualityScoresFactory, $this->idFactory);
    }

    public function test_it_returns_no_rows_when_given_no_rows(): void
    {
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);

        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder,
            [],
            'ecommerce',
            'en_US'
        );
        $this->assertSame([], $this->sut->__invoke($queryParameters, [], ''));
    }

    public function test_it_returns_product_row_with_additional_property_DQI_score(): void
    {
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);

        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder,
            [],
            'ecommerce',
            'en_US'
        );
        $productUuid1 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productUuid2 = 'ac930366-36f2-4ad9-9a9f-de94c913d8ca';
        $productModel1 = 42;
        $productModel2 = 69;
        $rows = [
                    $this->makeProductRow($productUuid1),
                    $this->makeProductRow($productUuid2),
                    $this->makeProductModelRow($productModel1),
                    $this->makeProductModelRow($productModel2),
                ];
        $this->idFactory->method('createCollection')->with([$productUuid1, $productUuid2])->willReturn($productIdCollection);
        $scores = [
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(96))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(36)),
                ];
        $this->getQualityScoresFactory->method('__invoke')->with($this->anything(), 'product')->willReturn($scores);
        $this->sut->__invoke($queryParameters, $rows, 'product')->shouldHaveScoreProperties();
    }

    public function test_it_returns_product_model_row_with_additional_property_DQI_score(): void
    {
        $productQueryBuilder = $this->createMock(ProductQueryBuilderInterface::class);
        $productIdCollection = $this->createMock(ProductEntityIdCollection::class);

        $queryParameters = new FetchProductAndProductModelRowsParameters(
            $productQueryBuilder,
            [],
            'ecommerce',
            'en_US'
        );
        $productUuid1 = '54162e35-ff81-48f1-96d5-5febd3f00fd5';
        $productUuid2 = 'ac930366-36f2-4ad9-9a9f-de94c913d8ca';
        $rows = [
                    $this->makeProductRow($productUuid1),
                    $this->makeProductRow($productUuid2),
                    $this->makeProductModelRow(1),
                    $this->makeProductModelRow(4),
                ];
        $this->idFactory->method('createCollection')->with(['1', '4'])->willReturn($productIdCollection);
        $scores = [
                    (new ChannelLocaleRateCollection())
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('en_US'), new Rate(96))
                        ->addRate(new ChannelCode('ecommerce'), new LocaleCode('fr_FR'), new Rate(36)),
                ];
        $this->getQualityScoresFactory->method('__invoke')->with($this->anything(), 'product_model')->willReturn($scores);
        $this->sut->__invoke($queryParameters, $rows, 'product_model')->shouldHaveScoreProperties();
    }

    private function makeProductRow(string $technicalId): Row
    {
        return Row::fromProduct(
            sprintf('product_or_product_model_%s', $technicalId),
            null,
            [],
            true,
            new \DateTime(),
            new \DateTime(),
            sprintf('Label of %s', $technicalId),
            null,
            null,
            $technicalId,
            null,
            new WriteValueCollection()
        );
    }

    private function makeProductModelRow(int $technicalId): Row
    {
        return Row::fromProductModel(
            sprintf('product_or_product_model_%s', $technicalId),
            'accessories',
            new \DateTime(),
            new \DateTime(),
            sprintf('Label of %s', $technicalId),
            null,
            $technicalId,
            [],
            null,
            new WriteValueCollection()
        );
    }

    // TODO: Custom matchers from getMatchers() need manual conversion
}
