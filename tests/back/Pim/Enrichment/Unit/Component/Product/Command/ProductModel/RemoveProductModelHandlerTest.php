<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Command\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelCommand;
use Akeneo\Pim\Enrichment\Component\Product\Command\ProductModel\RemoveProductModelHandler;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveProductModelHandlerTest extends TestCase
{
    private ProductModelRepositoryInterface|MockObject $productModelRepository;
    private RemoverInterface|MockObject $productModelRemover;
    private RemoveProductModelHandler $sut;

    protected function setUp(): void
    {
        $this->productModelRepository = $this->createMock(ProductModelRepositoryInterface::class);
        $this->productModelRemover = $this->createMock(RemoverInterface::class);
        $this->sut = new RemoveProductModelHandler($this->productModelRepository, $this->productModelRemover);
    }

    public function test_it_throws_when_the_product_model_does_not_exist(): void
    {
        $this->productModelRepository->method('findOneByIdentifier')->with('unknown')->willReturn(null);
        $this->productModelRemover->expects($this->never())->method('remove');

        $this->expectException(\InvalidArgumentException::class);

        ($this->sut)(new RemoveProductModelCommand('unknown'));
    }

    public function test_it_removes_the_product_model_matching_the_code(): void
    {
        $productModel = $this->createMock(ProductModelInterface::class);
        $this->productModelRepository->method('findOneByIdentifier')->with('a_product_model')->willReturn($productModel);
        $this->productModelRemover->expects($this->once())->method('remove')->with($productModel);

        ($this->sut)(new RemoveProductModelCommand('a_product_model'));
    }
}
