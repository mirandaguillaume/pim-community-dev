<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final readonly class Rows
{
    /**
     * @param \Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row[] $rows
     */
    public function __construct(private array $rows, private int $totalCount, private ?int $totalProductCount, private ?int $totalProductModelCount) {}

    public function rows(): array
    {
        return $this->rows;
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    public function totalProductCount(): ?int
    {
        return $this->totalProductCount;
    }

    public function totalProductModelCount(): ?int
    {
        return $this->totalProductModelCount;
    }
}
