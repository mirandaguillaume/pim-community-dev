<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchAttributeOptionsResult
{
    /** @var AttributeOption[] */
    private readonly array $items;

    public function __construct(array $items, private readonly int $matchesCount)
    {
        Assert::allIsInstanceOf($items, AttributeOption::class);

        $this->items = $items;
    }

    public function getMatchesCount(): int
    {
        return $this->matchesCount;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function normalize(): array
    {
        return [
            'matches_count' => $this->matchesCount,
            'items' => array_map(
                static fn (AttributeOption $attributeOption) => $attributeOption->normalize(),
                $this->items,
            ),
        ];
    }
}
