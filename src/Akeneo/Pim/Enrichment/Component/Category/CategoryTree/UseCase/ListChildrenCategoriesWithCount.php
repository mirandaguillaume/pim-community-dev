<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\UseCase;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListChildrenCategoriesWithCount
{
    public function __construct(private readonly int $categoryIdToExpand, private readonly int $categoryIdSelectedAsFilter, private readonly bool $countIncludingSubCategories, private readonly int $userId, private readonly string $translationLocaleCode)
    {
    }

    /**
     * The category to display is the category that is chosen by the user to be expanded.
     *
     * Do note that the user can expand a category without selecting it as a filter.
     * Therefore, the category to expand can be different from the selected category.
     */
    public function childrenCategoryIdToExpand(): int
    {
        return $this->categoryIdToExpand;
    }

    /**
     * This category is the category that is selected by the user to filter the product grid.
     * It is useful when:
     *  - the user displays the tree
     *  - selects a category as filter
     *  - goes onto another page
     *  - and then goes back onto the page to display the tree
     *
     * The tree has to be displayed with the category selected as filter, in order to not lose filters when browsing the application.
     *
     * So, we have to return all the children recursively until this selected category.
     * A better solution is to not reload entirely the tree on the front-end part and keep a state of it.
     */
    public function categoryIdSelectedAsFilter(): int
    {
        return $this->categoryIdSelectedAsFilter;
    }

    public function countIncludingSubCategories(): bool
    {
        return $this->countIncludingSubCategories;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function translationLocaleCode(): string
    {
        return $this->translationLocaleCode;
    }
}
