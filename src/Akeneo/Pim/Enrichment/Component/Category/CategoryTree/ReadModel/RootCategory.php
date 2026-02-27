<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;

/**
 * DTO representing the root categories in the category tree.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategory
{
    public function __construct(private readonly int $id, private readonly string $code, private readonly string $label, private readonly int $numberProductsInCategory, private readonly bool $selected) {}

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

    public function numberProductsInCategory(): int
    {
        return $this->numberProductsInCategory;
    }

    public function selected(): bool
    {
        return $this->selected;
    }
}
