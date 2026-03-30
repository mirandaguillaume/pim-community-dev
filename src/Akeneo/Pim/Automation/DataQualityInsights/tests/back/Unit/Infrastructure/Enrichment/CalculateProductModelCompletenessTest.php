<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Automation\DataQualityInsights\Infrastructure\Enrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\CompletenessCalculationResult;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\CalculateProductModelCompleteness;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Enrichment\GetProductModelAttributesMaskQueryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessProductMasks;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CalculateProductModelCompletenessTest extends TestCase
{
    private GetCompletenessProductMasks|MockObject $getCompletenessProductMasks;
    private GetProductModelAttributesMaskQueryInterface|MockObject $getProductModelAttributesMaskQuery;
    private ProductModelRepositoryInterface|MockObject $productModelRepository;
    private CalculateProductModelCompleteness $sut;

    protected function setUp(): void
    {
        $this->getCompletenessProductMasks = $this->createMock(GetCompletenessProductMasks::class);
        $this->getProductModelAttributesMaskQuery = $this->createMock(GetProductModelAttributesMaskQueryInterface::class);
        $this->productModelRepository = $this->createMock(ProductModelRepositoryInterface::class);
        $this->sut = new CalculateProductModelCompleteness($this->getCompletenessProductMasks, $this->getProductModelAttributesMaskQuery, $this->productModelRepository);
    }

    public function test_it_returns_an_empty_result_when_there_is_no_product_mask_to_apply(): void
    {
        $this->productModelRepository->method('find')->with(42)->willReturn(null);
        $this->assertEquals(new CompletenessCalculationResult(), $this->sut->calculate(new ProductModelId(42)));
    }

    public function test_it_throws_exception_when_product_model_id_is_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->calculate(ProductUuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'), );
    }

    public function test_it_returns_an_empty_result_when_there_is_no_attributes_mask_to_apply(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $family = $this->createMock(FamilyInterface::class);
        $values = $this->createMock(WriteValueCollection::class);
        $productMask = $this->createMock(CompletenessProductMask::class);

        $productModelId = new ProductModelId(42);
        $this->productModelRepository->method('find')->with(42)->willReturn($productModel);
        $productModel->method('getId')->willReturn(42);
        $productModel->method('getCode')->willReturn('a_product_model');
        $productModel->method('getFamily')->willReturn($family);
        $productModel->method('getValues')->willReturn($values);
        $family->method('getCode')->willReturn('a_family');
        $this->getCompletenessProductMasks->method('fromValueCollection')->with(42, 'a_family', $values)->willReturn($productMask);
        $this->getProductModelAttributesMaskQuery->method('execute')->with($productModelId)->willReturn(null);
        $this->assertEquals(new CompletenessCalculationResult(), $this->sut->calculate($productModelId));
    }
}
