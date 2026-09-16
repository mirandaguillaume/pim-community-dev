<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\RootCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;
use PHPUnit\Framework\TestCase;

/**
 * Guards the JSON returned by pim_enrich_product_grid_category_tree_listtree: the tree switcher of the product grid
 * reads id, code, the "Label (count)" label and a "true"/"false" string for the selected tree.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RootCategoryTest extends TestCase
{
    public function test_it_normalizes_the_root_categories_with_their_product_count_in_the_label(): void
    {
        $normalized = new RootCategory()->normalizeList([
            new ReadModel\RootCategory(1, 'master', 'Master catalog', 5, false),
            new ReadModel\RootCategory(2, 'print', 'Print catalog', 0, true),
        ]);

        $this->assertSame([
            [
                'id' => 1,
                'code' => 'master',
                'label' => 'Master catalog (5)',
                'selected' => 'false',
            ],
            [
                'id' => 2,
                'code' => 'print',
                'label' => 'Print catalog (0)',
                'selected' => 'true',
            ],
        ], $normalized);
    }

    public function test_it_keeps_the_fallback_label_of_an_untranslated_tree(): void
    {
        $normalized = new RootCategory()->normalizeList([
            new ReadModel\RootCategory(3, 'master_china', '[master_china]', 12, true),
        ]);

        $this->assertSame('[master_china] (12)', $normalized[0]['label']);
    }

    public function test_it_normalizes_no_root_category_as_an_empty_list(): void
    {
        $this->assertSame([], new RootCategory()->normalizeList([]));
    }
}
