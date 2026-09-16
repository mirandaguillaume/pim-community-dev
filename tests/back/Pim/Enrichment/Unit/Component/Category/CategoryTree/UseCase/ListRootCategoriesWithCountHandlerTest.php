<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListRootCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\RootCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCount;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase\ListRootCategoriesWithCountHandler;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Guards the tree panel of the product grid (GET pim_enrich_product_grid_category_tree_listtree): the handler picks
 * the tree to expand and dispatches to the query matching the "include sub-categories" switch.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListRootCategoriesWithCountHandlerTest extends TestCase
{
    private const string LOCALE = 'en_US';
    private const int USER_ID = 42;

    private CategoryRepositoryInterface|MockObject $categoryRepository;
    private UserContext|MockObject $userContext;
    private ListRootCategoriesWithCountIncludingSubCategories|MockObject $listIncludingSubCategories;
    private ListRootCategoriesWithCountNotIncludingSubCategories|MockObject $listNotIncludingSubCategories;
    private ListRootCategoriesWithCountHandler $sut;

    /** @var array<int, CategoryInterface> */
    private array $categoriesById = [];

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->categoryRepository->method('find')->willReturnCallback(
            fn($id): ?CategoryInterface => $this->categoriesById[$id] ?? null
        );
        $this->userContext = $this->createMock(UserContext::class);
        $this->listIncludingSubCategories = $this->createMock(ListRootCategoriesWithCountIncludingSubCategories::class);
        $this->listNotIncludingSubCategories = $this->createMock(ListRootCategoriesWithCountNotIncludingSubCategories::class);

        $this->sut = new ListRootCategoriesWithCountHandler(
            $this->categoryRepository,
            $this->userContext,
            $this->listIncludingSubCategories,
            $this->listNotIncludingSubCategories
        );
    }

    public function test_it_counts_including_sub_categories_for_the_tree_selected_as_filter(): void
    {
        $this->givenACategory(7, 7);
        $rootCategories = [new RootCategory(7, 'tree', 'Tree', 3, true)];

        $this->userContext->expects($this->never())->method('getAccessibleUserTree');
        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 7)
            ->willReturn($rootCategories);
        $this->listNotIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame($rootCategories, $this->sut->handle($this->query(-1, true, 7)));
    }

    public function test_it_counts_not_including_sub_categories_for_the_tree_selected_as_filter(): void
    {
        $this->givenACategory(7, 7);
        $rootCategories = [new RootCategory(7, 'tree', 'Tree', 0, true)];

        $this->listNotIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 7)
            ->willReturn($rootCategories);
        $this->listIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame($rootCategories, $this->sut->handle($this->query(-1, false, 7)));
    }

    public function test_it_expands_the_root_of_the_category_selected_as_filter(): void
    {
        $this->givenACategory(3, 3);
        $this->givenACategory(12, 3);

        $this->userContext->expects($this->never())->method('getAccessibleUserTree');
        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 3)
            ->willReturn([]);

        $this->sut->handle($this->query(12, true, null));
    }

    public function test_the_root_of_an_existing_selected_category_wins_over_the_tree_selected_as_filter(): void
    {
        $this->givenACategory(3, 3);
        $this->givenACategory(7, 7);
        $this->givenACategory(12, 3);

        $this->listNotIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 3)
            ->willReturn([]);
        $this->listIncludingSubCategories->expects($this->never())->method('list');

        $this->sut->handle($this->query(12, false, 7));
    }

    public function test_it_falls_back_to_the_accessible_user_tree_when_no_tree_nor_category_is_selected(): void
    {
        $userTree = $this->givenACategory(5, 5);
        $this->userContext->expects($this->once())->method('getAccessibleUserTree')->willReturn($userTree);

        $this->listIncludingSubCategories->expects($this->once())
            ->method('list')
            ->with(self::LOCALE, self::USER_ID, 5)
            ->willReturn([]);

        $this->sut->handle($this->query(-1, true, null));
    }

    public function test_it_returns_no_category_when_the_user_has_no_accessible_tree(): void
    {
        $this->userContext->method('getAccessibleUserTree')->willReturn(null);

        $this->listIncludingSubCategories->expects($this->never())->method('list');
        $this->listNotIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame([], $this->sut->handle($this->query(-1, true, null)));
    }

    public function test_it_returns_no_category_when_the_tree_selected_as_filter_does_not_exist(): void
    {
        $this->userContext->expects($this->never())->method('getAccessibleUserTree');
        $this->listIncludingSubCategories->expects($this->never())->method('list');
        $this->listNotIncludingSubCategories->expects($this->never())->method('list');

        $this->assertSame([], $this->sut->handle($this->query(-1, false, 99)));
    }

    private function query(int $selectedCategoryId, bool $includeSub, ?int $selectedTreeId): ListRootCategoriesWithCount
    {
        return new ListRootCategoriesWithCount($selectedCategoryId, $includeSub, self::USER_ID, self::LOCALE, $selectedTreeId);
    }

    private function givenACategory(int $id, int $rootId): CategoryInterface
    {
        $category = $this->createMock(CategoryInterface::class);
        $category->method('getId')->willReturn($id);
        $category->method('getRoot')->willReturn($rootId);
        $this->categoriesById[$id] = $category;

        return $category;
    }
}
