<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\ItemCategoryRepositoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductModelCategoryController;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The listing behind the product model edit form's Categories tab. categories.js groups `categories` by rootId to
 * count each tree badge and to tick the tree as soon as the tab loads, so an inherited category must be listed with
 * the root of its own tree.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCategoryControllerTest extends TestCase
{
    private ProductModelRepositoryInterface|MockObject $productModelRepository;
    private ItemCategoryRepositoryInterface|MockObject $productModelCategoryRepository;
    private ObjectFilterInterface|MockObject $objectFilter;
    private ProductModelCategoryController $sut;

    protected function setUp(): void
    {
        $this->productModelRepository = $this->createMock(ProductModelRepositoryInterface::class);
        $this->productModelCategoryRepository = $this->createMock(ItemCategoryRepositoryInterface::class);
        $this->objectFilter = $this->createMock(ObjectFilterInterface::class);
        $this->sut = new ProductModelCategoryController(
            $this->productModelRepository,
            $this->productModelCategoryRepository,
            $this->objectFilter,
        );
    }

    public function test_it_throws_a_not_found_exception_when_the_product_model_does_not_exist(): void
    {
        $this->productModelRepository->method('find')->with('42')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Product model with ID "42" could not be found.');

        $this->sut->listAction('42');
    }

    public function test_it_lists_the_visible_trees_and_the_own_and_inherited_categories_of_the_product_model(): void
    {
        $masterTree = $this->category(1, 'master', 'Master catalog');
        $hiddenTree = $this->category(2, 'hidden_tree', 'Hidden tree');
        $salesTree = $this->category(3, 'sales', 'Sales catalog');

        $productModel = $this->createMock(ProductModelInterface::class);
        $this->productModelRepository->method('find')->with('7')->willReturn($productModel);
        // The item count only covers the model's own categories: an inherited category does not mark its tree.
        $this->productModelCategoryRepository->method('getItemCountByTree')->with($productModel)->willReturn([
            ['tree' => $masterTree, 'itemCount' => 2],
            ['tree' => $hiddenTree, 'itemCount' => 5],
            ['tree' => $salesTree, 'itemCount' => 0],
        ]);
        $this->objectFilter->method('filterObject')->willReturnCallback(
            fn(CategoryInterface $category, string $type): bool =>
                'pim.internal_api.product_category.view' === $type && $category === $hiddenTree
        );
        // ProductModel::getCategories of a sub product model: its own categories, then the ones of its parent.
        $productModel->method('getCategories')->willReturn(new ArrayCollection([
            $this->category(10, 'summer', 'Summer', 1),
            $this->category(11, 'spring', 'Spring', 1),
            $this->category(12, 'tshirts', 'T-shirts', 3),
        ]));

        $response = $this->sut->listAction('7');
        $data = \json_decode((string) $response->getContent(), true);

        $this->assertSame([
            ['id' => 1, 'code' => 'master', 'label' => 'Master catalog', 'associated' => true],
            ['id' => 3, 'code' => 'sales', 'label' => 'Sales catalog', 'associated' => false],
        ], $data['trees']);
        $this->assertSame([
            ['id' => 10, 'code' => 'summer', 'rootId' => 1],
            ['id' => 11, 'code' => 'spring', 'rootId' => 1],
            ['id' => 12, 'code' => 'tshirts', 'rootId' => 3],
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
