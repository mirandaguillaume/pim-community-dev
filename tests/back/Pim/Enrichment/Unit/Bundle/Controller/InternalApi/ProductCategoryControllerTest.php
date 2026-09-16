<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductCategoryController;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryControllerTest extends TestCase
{
    private ProductRepositoryInterface|MockObject $productRepository;
    private ProductCategoryRepositoryInterface|MockObject $productCategoryRepository;
    private ObjectFilterInterface|MockObject $objectFilter;
    private ProductCategoryController $sut;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->productCategoryRepository = $this->createMock(ProductCategoryRepositoryInterface::class);
        $this->objectFilter = $this->createMock(ObjectFilterInterface::class);
        $this->sut = new ProductCategoryController(
            $this->productRepository,
            $this->productCategoryRepository,
            $this->objectFilter,
        );
    }

    public function test_it_throws_a_not_found_exception_when_the_product_does_not_exist(): void
    {
        $this->productRepository->method('find')->with('unknown_uuid')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->sut->listAction('unknown_uuid');
    }

    public function test_it_lists_the_trees_and_categories_of_the_product_excluding_filtered_out_categories(): void
    {
        $visibleCategory = $this->category(1, 'visible_tree', 'Visible tree');
        $filteredOutCategory = $this->category(2, 'hidden_tree', 'Hidden tree');
        $productCategory = $this->category(3, 'a_category', 'A category', 1);

        $product = $this->createMock(ProductInterface::class);
        $this->productRepository->method('find')->with('a_uuid')->willReturn($product);
        $this->productCategoryRepository->method('getItemCountByTree')->with($product)->willReturn([
            ['tree' => $visibleCategory, 'itemCount' => 3],
            ['tree' => $filteredOutCategory, 'itemCount' => 0],
        ]);
        $this->objectFilter->method('filterObject')
            ->willReturnCallback(fn(CategoryInterface $category): bool => $category === $filteredOutCategory);
        $product->method('getCategories')->willReturn(new ArrayCollection([$productCategory]));

        $response = $this->sut->listAction('a_uuid');
        $data = \json_decode((string) $response->getContent(), true);

        $this->assertSame([
            ['id' => 1, 'code' => 'visible_tree', 'label' => 'Visible tree', 'associated' => true],
        ], $data['trees']);
        $this->assertSame([
            ['id' => 3, 'code' => 'a_category', 'rootId' => 1],
        ], $data['categories']);
    }

    private function category(int $id, string $code, string $label, ?int $rootId = null): CategoryInterface|MockObject
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('getId')->willReturn($id);
        $category->method('getCode')->willReturn($code);
        $category->method('getLabel')->willReturn($label);
        $category->method('getRoot')->willReturn($rootId);

        return $category;
    }
}
