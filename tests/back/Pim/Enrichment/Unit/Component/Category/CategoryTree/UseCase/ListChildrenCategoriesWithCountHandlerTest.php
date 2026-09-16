<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListChildrenCategoriesWithCountHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Guards the node expansion of the product grid tree panel (GET pim_enrich_product_grid_category_tree_children): the
 * handler resolves the category to expand, keeps the category selected as filter only when it belongs to it, and
 * dispatches to the query matching the "include sub-categories" switch.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCountHandlerTest extends TestCase
{
    private const string LOCALE = 'en_US';
    private const int USER_ID = 42;

    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private UserContext|MockObject $userContext;
    private ListChildrenCategoriesWithCountIncludingSubCategories|MockObject $listIncludingSubCategories;
    private ListChildrenCategoriesWithCountNotIncludingSubCategories|MockObject $listNotIncludingSubCategories;
    private ListChildrenCategoriesWithCountHandler $sut;

    /** @var array<int, CategoryInterface> */
    private array $categoriesById = [];

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->categoryRepository->method('find')->willReturnCallback(
            fn($id): ?CategoryInterface => $this->categoriesById[$id] ?? null
        );
        $this->userContext = $this->createMock(UserContext::class);
        $this->listIncludingSubCategories = $this->createMock(ListChildrenCategoriesWithCountIncludingSubCategories::class);
        $this->listNotIncludingSubCategories = $this->createMock(ListChildrenCategoriesWithCountNotIncludingSubCategories::class);

        $this->sut = new ListChildrenCategoriesWithCountHandler(
            $this->categoryRepository,
            $this->userContext,
            $this->listIncludingSubCategories,
            $this->listNotIncludingSubCategories
        );
    }

    public function test_it_counts_including_sub_categories_for_the_children_of_the_category_to_expand(): void
    {
        $this->givenACategory(10);
        $children = [new ChildCategory(11, 'men_summer', 'Men summer', false, true, 1, [])];

        $this->userContext->expects($this->never())->method('getUserProductCategoryTree');
        $this->categoryRepository->expects($this->never())->method('isAncestor');
        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 10, null)
            ->willReturn($children);
        $this->listNotIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame($children, $this->sut->handle($this->query(10, -1, true)));
    }

    public function test_it_counts_not_including_sub_categories_for_the_children_of_the_category_to_expand(): void
    {
        $this->givenACategory(10);
        $children = [new ChildCategory(11, 'men_summer', 'Men summer', false, true, 0, [])];

        $this->listNotIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 10, null)
            ->willReturn($children);
        $this->listIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame($children, $this->sut->handle($this->query(10, -1, false)));
    }

    public function test_it_expands_the_user_product_category_tree_when_no_category_to_expand_is_given(): void
    {
        $userTree = $this->createCategory(1);
        $this->categoryRepository->expects($this->never())->method('find');
        $this->userContext->expects($this->once())->method('getUserProductCategoryTree')->willReturn($userTree);

        $this->listNotIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 1, null)
            ->willReturn([]);

        $this->sut->handle($this->query(-1, -1, false));
    }

    public function test_it_expands_the_user_product_category_tree_when_the_category_to_expand_does_not_exist(): void
    {
        $userTree = $this->createCategory(1);
        $this->userContext->expects($this->once())->method('getUserProductCategoryTree')->willReturn($userTree);

        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 1, null)
            ->willReturn([]);

        $this->sut->handle($this->query(404, -1, true));
    }

    public function test_it_expands_down_to_the_category_selected_as_filter_when_it_belongs_to_the_category_to_expand(): void
    {
        $categoryToExpand = $this->givenACategory(10);
        $categorySelectedAsFilter = $this->givenACategory(20);

        $this->categoryRepository->expects($this->once())
            ->method('isAncestor')
            ->with($categoryToExpand, $categorySelectedAsFilter)
            ->willReturn(true);
        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 10, 20)
            ->willReturn([]);

        $this->sut->handle($this->query(10, 20, true));
    }

    public function test_it_ignores_a_category_selected_as_filter_outside_of_the_category_to_expand(): void
    {
        $this->givenACategory(10);
        $this->givenACategory(20);

        $this->categoryRepository->method('isAncestor')->willReturn(false);
        $this->listNotIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 10, null)
            ->willReturn([]);

        $this->sut->handle($this->query(10, 20, false));
    }

    public function test_it_ignores_a_category_selected_as_filter_that_does_not_exist(): void
    {
        $this->givenACategory(10);

        $this->categoryRepository->expects($this->never())->method('isAncestor');
        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 10, null)
            ->willReturn([]);

        $this->sut->handle($this->query(10, 404, true));
    }

    private function query(int $categoryToExpandId, int $selectedCategoryId, bool $includeSub): ListChildrenCategoriesWithCount
    {
        return new ListChildrenCategoriesWithCount(
            $categoryToExpandId,
            $selectedCategoryId,
            $includeSub,
            self::USER_ID,
            self::LOCALE
        );
    }

    private function givenACategory(int $id): CategoryInterface
    {
        $category = $this->createCategory($id);
        $this->categoriesById[$id] = $category;

        return $category;
    }

    private function createCategory(int $id): CategoryInterface
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('getId')->willReturn($id);

        return $category;
    }
}
