<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

use Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Query\ListChildrenCategoriesWithCountNotIncludingSubCategories;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCountHandler
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository, private readonly UserContext $userContext, private readonly ListChildrenCategoriesWithCountIncludingSubCategories $listAndCountIncludingSubCategories, private readonly ListChildrenCategoriesWithCountNotIncludingSubCategories $listAndCountNotIncludingSubCategories)
    {
    }

    /**
     * @return ChildCategory[]
     */
    public function handle(ListChildrenCategoriesWithCount $query): array
    {
        $categoryToExpand = -1 !== $query->childrenCategoryIdToExpand()
            ? $this->categoryRepository->find($query->childrenCategoryIdToExpand()) : null;

        if (null === $categoryToExpand) {
            $categoryToExpand = $this->userContext->getUserProductCategoryTree();
        }

        $categorySelectedAsFilter = -1 !== $query->categoryIdSelectedAsFilter()
            ? $this->categoryRepository->find($query->categoryIdSelectedAsFilter()) : null;

        if (null !== $categorySelectedAsFilter
            && !$this->categoryRepository->isAncestor($categoryToExpand, $categorySelectedAsFilter)) {
            $categorySelectedAsFilter = null;
        }

        $categoryIdSelectedAsFilter = null !== $categorySelectedAsFilter ? $categorySelectedAsFilter->getId() : null;

        $categories = $query->countIncludingSubCategories()
            ? $this->listAndCountIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $categoryToExpand->getId(),
                $categoryIdSelectedAsFilter
            )
            : $this->listAndCountNotIncludingSubCategories->list(
                $query->translationLocaleCode(),
                $query->userId(),
                $categoryToExpand->getId(),
                $categoryIdSelectedAsFilter
            );

        return $categories;
    }
}
