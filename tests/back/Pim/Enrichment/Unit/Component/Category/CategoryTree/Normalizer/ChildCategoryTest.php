<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Category\CategoryTree\Normalizer;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\Normalizer\ChildCategory;
use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel;
use PHPUnit\Framework\TestCase;

/**
 * Guards the jstree JSON returned by pim_enrich_product_grid_category_tree_children: node id and code attributes,
 * the "Label (count)" text, the leaf/closed/open state with the "toselect" marker, and the recursively expanded
 * children.
 *
 * @copyright 2026 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChildCategoryTest extends TestCase
{
    public function test_it_normalizes_the_children_with_their_product_count_and_state(): void
    {
        $normalized = new ChildCategory()->normalizeList([
            new ReadModel\ChildCategory(2, 'men', 'Men', false, false, 0, []),
            new ReadModel\ChildCategory(3, 'women', 'Women', false, true, 1, []),
        ]);

        $this->assertSame([
            [
                'attr' => ['id' => 'node_2', 'data-code' => 'men'],
                'data' => 'Men (0)',
                'state' => 'closed',
                'children' => [],
            ],
            [
                'attr' => ['id' => 'node_3', 'data-code' => 'women'],
                'data' => 'Women (1)',
                'state' => 'leaf',
                'children' => [],
            ],
        ], $normalized);
    }

    public function test_it_opens_the_categories_expanded_down_to_the_category_selected_as_filter(): void
    {
        $normalized = new ChildCategory()->normalizeList([
            new ReadModel\ChildCategory(2, 'men', 'Men', false, false, 3, [
                new ReadModel\ChildCategory(4, 'men_summer', 'Men summer', true, true, 1, []),
                new ReadModel\ChildCategory(5, 'men_winter', 'Men winter', false, true, 2, []),
            ]),
        ]);

        $this->assertSame([
            [
                'attr' => ['id' => 'node_2', 'data-code' => 'men'],
                'data' => 'Men (3)',
                'state' => 'open',
                'children' => [
                    [
                        'attr' => ['id' => 'node_4', 'data-code' => 'men_summer'],
                        'data' => 'Men summer (1)',
                        'state' => 'leaf toselect',
                        'children' => [],
                    ],
                    [
                        'attr' => ['id' => 'node_5', 'data-code' => 'men_winter'],
                        'data' => 'Men winter (2)',
                        'state' => 'leaf',
                        'children' => [],
                    ],
                ],
            ],
        ], $normalized);
    }

    public function test_it_marks_a_closed_or_open_category_selected_as_filter_to_select(): void
    {
        $normalized = new ChildCategory()->normalizeList([
            new ReadModel\ChildCategory(2, 'men', 'Men', true, false, 0, []),
            new ReadModel\ChildCategory(6, 'women', 'Women', true, false, 1, [
                new ReadModel\ChildCategory(7, 'women_summer', 'Women summer', false, true, 1, []),
            ]),
        ]);

        $this->assertSame('closed toselect', $normalized[0]['state']);
        $this->assertSame('open toselect', $normalized[1]['state']);
    }

    public function test_it_normalizes_no_child_category_as_an_empty_list(): void
    {
        $this->assertSame([], new ChildCategory()->normalizeList([]));
    }
}
