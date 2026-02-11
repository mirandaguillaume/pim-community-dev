<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category;

/**
 * Placeholder implementation to provide granted category codes.
 */
final class GetGrantedCategoryCodes
{
    /**
     * @param array<int|string> $groupIds
     * @return string[]
     */
    public function forGroupIds(array $groupIds): array
    {
        return [];
    }
}
