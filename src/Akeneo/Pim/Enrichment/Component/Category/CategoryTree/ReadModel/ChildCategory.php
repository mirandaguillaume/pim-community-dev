<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

/**
 * DTO representing a category to expand in the tree, with all the children to expand as well.
 * As the children to expand are the same DTO, the tree can be recursively expanded until a given depth.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChildCategory
{
    /**
     * @param ChildCategory[] $childrenCategoriesToExpand
     */
    public function __construct(private readonly int $id, private readonly string $code, private readonly string $label, private readonly bool $selectedAsFilter, private readonly bool $isLeaf, private readonly int $numberProductsInCategory, private readonly array $childrenCategoriesToExpand) {}

    public function id(): int
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function selectedAsFilter(): bool
    {
        return $this->selectedAsFilter;
    }

    public function isLeaf(): bool
    {
        return $this->isLeaf;
    }

    public function expanded(): bool
    {
        return !empty($this->childrenCategoriesToExpand);
    }

    public function numberProductsInCategory(): int
    {
        return $this->numberProductsInCategory;
    }

    /**
     * @return ChildCategory[]
     */
    public function childrenCategoriesToExpand(): array
    {
        return $this->childrenCategoriesToExpand;
    }
}
