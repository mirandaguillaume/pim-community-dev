<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Cli\CheckCategoryTrees;

use Akeneo\Category\Infrastructure\Cli\CheckCategoryTrees\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $sut;

    protected function setUp(): void
    {
        $this->sut = new Category($this->getMasterCategory());
    }

    public function test_it_creates_category_with_correct_id(): void
    {
        $this->assertSame(1, $this->sut->getId());
    }

    public function test_it_creates_category_with_correct_code(): void
    {
        $this->assertSame('master', $this->sut->getCode());
    }

    public function test_it_creates_category_with_null_parent_id(): void
    {
        $this->assertNull($this->sut->getParentId());
    }

    public function test_it_creates_category_with_correct_root_id(): void
    {
        $this->assertSame(1, $this->sut->getRootId());
    }

    public function test_it_creates_category_with_correct_level(): void
    {
        $this->assertSame(0, $this->sut->getLevel());
    }

    public function test_it_creates_category_with_correct_left(): void
    {
        $this->assertSame(1, $this->sut->getLeft());
    }

    public function test_it_creates_category_with_correct_right(): void
    {
        $this->assertSame(8, $this->sut->getRight());
    }

    public function test_it_is_not_linked_by_default(): void
    {
        $this->assertFalse($this->sut->isLinked());
    }

    public function test_it_has_no_children_by_default(): void
    {
        $this->assertSame([], $this->sut->getChildren());
    }

    public function test_it_adds_a_child(): void
    {
        $child = new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child1', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->sut->addChild($child);
        $this->assertCount(1, $this->sut->getChildren());
        $this->assertSame($child, $this->sut->getChildren()[0]);
    }

    public function test_get_child_at_returns_child(): void
    {
        $child = new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child1', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->sut->addChild($child);
        $this->assertSame($child, $this->sut->getChildAt(0));
    }

    public function test_get_child_at_returns_null_for_invalid_index(): void
    {
        $this->assertNull($this->sut->getChildAt(0));
        $this->assertNull($this->sut->getChildAt(99));
    }

    public function test_set_left(): void
    {
        $this->sut->setLeft(10);
        $this->assertSame(10, $this->sut->getLeft());
    }

    public function test_set_right(): void
    {
        $this->sut->setRight(20);
        $this->assertSame(20, $this->sut->getRight());
    }

    public function test_set_level(): void
    {
        $this->sut->setLevel(3);
        $this->assertSame(3, $this->sut->getLevel());
    }

    public function test_it_creates_category_with_integer_parent_id(): void
    {
        $cat = new Category([
            'id' => 5, 'parent_id' => 2, 'root' => 1,
            'code' => 'sub', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->assertSame(2, $cat->getParentId());
    }

    public function test_it_creates_category_with_null_root(): void
    {
        $cat = new Category([
            'id' => 5, 'parent_id' => null, 'root' => null,
            'code' => 'orphan', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $this->assertNull($cat->getRootId());
    }

    public function test_it_reorders_the_category_tree(): void
    {
        $this->sut->addChild($this->getDisorderedCategories()['child1']);
        $this->sut->addChild($this->getDisorderedCategories()['child2']);
        $this->sut->addChild($this->getDisorderedCategories()['child3']);

        $reordered = $this->sut->reorder();

        // Verify the reordered category has correct lft/rgt/lvl
        $this->assertSame(1, $reordered->getLeft());
        $this->assertSame(8, $reordered->getRight());
        $this->assertSame(0, $reordered->getLevel());
        $this->assertCount(3, $reordered->getChildren());

        // child1 should be lft=2, rgt=3, lvl=1
        $this->assertSame(2, $reordered->getChildAt(0)->getLeft());
        $this->assertSame(3, $reordered->getChildAt(0)->getRight());
        $this->assertSame(1, $reordered->getChildAt(0)->getLevel());

        // child2 should be lft=4, rgt=5, lvl=1
        $this->assertSame(4, $reordered->getChildAt(1)->getLeft());
        $this->assertSame(5, $reordered->getChildAt(1)->getRight());
        $this->assertSame(1, $reordered->getChildAt(1)->getLevel());

        // child3 should be lft=6, rgt=7, lvl=1
        $this->assertSame(6, $reordered->getChildAt(2)->getLeft());
        $this->assertSame(7, $reordered->getChildAt(2)->getRight());
        $this->assertSame(1, $reordered->getChildAt(2)->getLevel());

        // Also verify with assertEquals for full tree comparison
        $expectedCategory = new Category($this->getMasterCategory());
        $expectedCategory->addChild($this->getCategories()['child1']);
        $expectedCategory->addChild($this->getCategories()['child2']);
        $expectedCategory->addChild($this->getCategories()['child3']);
        $this->assertEquals($expectedCategory, $reordered);
    }

    public function test_reorder_preserves_category_id_and_code(): void
    {
        $this->sut->addChild($this->getDisorderedCategories()['child1']);
        $reordered = $this->sut->reorder();

        $this->assertSame(1, $reordered->getId());
        $this->assertSame('master', $reordered->getCode());
        $this->assertSame(2, $reordered->getChildAt(0)->getId());
        $this->assertSame('child1', $reordered->getChildAt(0)->getCode());
    }

    public function test_reorder_with_no_children(): void
    {
        $reordered = $this->sut->reorder();
        $this->assertSame(1, $reordered->getLeft());
        $this->assertSame(2, $reordered->getRight());
        $this->assertSame(0, $reordered->getLevel());
        $this->assertCount(0, $reordered->getChildren());
    }

    public function test_reorder_does_not_mutate_original(): void
    {
        $child = $this->getDisorderedCategories()['child1'];
        $this->sut->addChild($child);
        $originalLeft = $this->sut->getLeft();
        $originalRight = $this->sut->getRight();

        $this->sut->reorder();

        // Original should be unchanged
        $this->assertSame($originalLeft, $this->sut->getLeft());
        $this->assertSame($originalRight, $this->sut->getRight());
    }

    public function test_diff_returns_empty_for_identical_categories(): void
    {
        $cat1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $cat2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $this->assertSame([], $cat1->diff($cat2));
    }

    public function test_diff_detects_level_mismatch(): void
    {
        $cat1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $cat2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '1', 'lft' => '1', 'rgt' => '2',
        ]);
        $diffs = $cat1->diff($cat2);
        $this->assertCount(1, $diffs);
        $this->assertStringContainsString('Level mismatch', $diffs[0]);
        $this->assertStringContainsString('has:0', $diffs[0]);
        $this->assertStringContainsString('expected:1', $diffs[0]);
    }

    public function test_diff_detects_left_mismatch(): void
    {
        $cat1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $cat2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '0', 'lft' => '5', 'rgt' => '2',
        ]);
        $diffs = $cat1->diff($cat2);
        $this->assertCount(1, $diffs);
        $this->assertStringContainsString('Left mismatch', $diffs[0]);
        $this->assertStringContainsString('has:1', $diffs[0]);
        $this->assertStringContainsString('expected:5', $diffs[0]);
    }

    public function test_diff_detects_right_mismatch(): void
    {
        $cat1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $cat2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'test', 'lvl' => '0', 'lft' => '1', 'rgt' => '10',
        ]);
        $diffs = $cat1->diff($cat2);
        $this->assertCount(1, $diffs);
        $this->assertStringContainsString('Right mismatch', $diffs[0]);
        $this->assertStringContainsString('has:2', $diffs[0]);
        $this->assertStringContainsString('expected:10', $diffs[0]);
    }

    public function test_diff_detects_children_count_mismatch(): void
    {
        $parent1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '4',
        ]);
        $parent1->addChild(new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]));

        $parent2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '4',
        ]);

        $diffs = $parent1->diff($parent2);
        $this->assertNotEmpty($diffs);
        $foundChildrenMismatch = false;
        foreach ($diffs as $diff) {
            if (str_contains($diff, 'Children count mismatch')) {
                $foundChildrenMismatch = true;
                $this->assertStringContainsString('has:1', $diff);
                $this->assertStringContainsString('expected:0', $diff);
            }
        }
        $this->assertTrue($foundChildrenMismatch);
    }

    public function test_diff_includes_child_context_prefix(): void
    {
        $parent1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '4',
        ]);
        $parent1->addChild(new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]));

        $parent2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'master', 'lvl' => '0', 'lft' => '1', 'rgt' => '4',
        ]);
        $parent2->addChild(new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child', 'lvl' => '1', 'lft' => '2', 'rgt' => '99',
        ]));

        $diffs = $parent1->diff($parent2);
        $this->assertNotEmpty($diffs);
        $this->assertStringContainsString('Child at index 0:', $diffs[0]);
        $this->assertStringContainsString('Right mismatch', $diffs[0]);
    }

    public function test_it_displays_diff_between_categories(): void
    {
        $this->sut->addChild($this->getDisorderedCategories()['child1']);
        $this->sut->addChild($this->getDisorderedCategories()['child2']);
        $this->sut->addChild($this->getDisorderedCategories()['child3']);
        $this->sut->addChild($this->getDisorderedCategories()['child4']);
        $expectedCategory = new Category($this->getMasterCategory());
        $expectedCategory->addChild($this->getCategories()['child1']);
        $expectedCategory->addChild($this->getCategories()['child2']);
        $expectedCategory->addChild($this->getCategories()['child3']);
        $diffs = $this->sut->diff($expectedCategory);
        $this->assertCount(5, $diffs);
        $this->assertSame("id=1 code=master : Children count mismatch (has:4, expected:3)", $diffs[0]);
        $this->assertSame("Child at index 0: id=2 code=child1 : Left mismatch (has:3, expected:2)", $diffs[1]);
        $this->assertSame("Child at index 0: id=2 code=child1 : Right mismatch (has:2, expected:3)", $diffs[2]);
        $this->assertSame("Child at index 1: id=3 code=child2 : Right mismatch (has:7, expected:5)", $diffs[3]);
        $this->assertSame("Child at index 2: id=4 code=child3 : Level mismatch (has:2, expected:1)", $diffs[4]);
    }

    public function test_dump_nodes_single_level(): void
    {
        $rows = $this->sut->dumpNodes(0, 0);
        $this->assertCount(1, $rows);
        $this->assertStringContainsString('(1,master,lvl=0,lft=1,rgt=8)', $rows[0]);
    }

    public function test_dump_nodes_with_children(): void
    {
        $child = new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child1', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->sut->addChild($child);
        $rows = $this->sut->dumpNodes(0, 1);
        $this->assertCount(2, $rows);
        $this->assertStringContainsString('master', $rows[0]);
        $this->assertStringContainsString('child1', $rows[1]);
        // Child should be indented with a tab
        $this->assertStringStartsWith("\t", $rows[1]);
    }

    public function test_dump_nodes_respects_max_level(): void
    {
        $child = new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child1', 'lvl' => '1', 'lft' => '2', 'rgt' => '5',
        ]);
        $grandchild = new Category([
            'id' => 3, 'parent_id' => 2, 'root' => 1,
            'code' => 'gc', 'lvl' => '2', 'lft' => '3', 'rgt' => '4',
        ]);
        $child->addChild($grandchild);
        $this->sut->addChild($child);

        // maxLevel=0 should only show root (level 0 < 0 is false, no children expanded)
        $rows = $this->sut->dumpNodes(0, 0);
        $this->assertCount(1, $rows);

        // maxLevel=1 should show root + child (root level 0 < 1, expands; child recursive call uses default maxLevel=1, so 1<1 is false)
        $rows = $this->sut->dumpNodes(0, 1);
        $this->assertCount(2, $rows);
        $this->assertStringContainsString('child1', $rows[1]);
    }

    public function test_parent_id_is_cast_to_int(): void
    {
        // When parent_id comes as a string from DB, it must be cast to int
        $cat = new Category([
            'id' => 5, 'parent_id' => '2', 'root' => '1',
            'code' => 'sub', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->assertSame(2, $cat->getParentId());
        $this->assertIsInt($cat->getParentId());
    }

    public function test_root_id_is_cast_to_int(): void
    {
        $cat = new Category([
            'id' => 5, 'parent_id' => null, 'root' => '10',
            'code' => 'sub', 'lvl' => '0', 'lft' => '1', 'rgt' => '2',
        ]);
        $this->assertSame(10, $cat->getRootId());
        $this->assertIsInt($cat->getRootId());
    }

    public function test_lvl_lft_rgt_are_cast_to_int(): void
    {
        $cat = new Category([
            'id' => 5, 'parent_id' => null, 'root' => null,
            'code' => 'test', 'lvl' => '3', 'lft' => '7', 'rgt' => '14',
        ]);
        $this->assertSame(3, $cat->getLevel());
        $this->assertIsInt($cat->getLevel());
        $this->assertSame(7, $cat->getLeft());
        $this->assertIsInt($cat->getLeft());
        $this->assertSame(14, $cat->getRight());
        $this->assertIsInt($cat->getRight());
    }

    public function test_dump_nodes_default_level_produces_no_indentation_at_root(): void
    {
        // Default level=0 means no tabs for root
        $rows = $this->sut->dumpNodes();
        $this->assertCount(1, $rows);
        // At level 0 there should be no leading tab
        $this->assertStringStartsWith('(', $rows[0]);
    }

    public function test_dump_nodes_with_level_1_produces_one_tab(): void
    {
        $rows = $this->sut->dumpNodes(1, 0);
        $this->assertCount(1, $rows);
        // At level 1 there should be one leading tab
        $this->assertStringStartsWith("\t(", $rows[0]);
    }

    public function test_dump_nodes_with_level_2_produces_two_tabs(): void
    {
        $rows = $this->sut->dumpNodes(2, 0);
        $this->assertCount(1, $rows);
        $this->assertStringStartsWith("\t\t(", $rows[0]);
    }

    public function test_dump_nodes_with_children_increments_level_by_one(): void
    {
        $child = new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'child1', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]);
        $this->sut->addChild($child);

        // When calling at level 0 with maxLevel 2, child at level+1=1 should have one tab
        $rows = $this->sut->dumpNodes(0, 2);
        $this->assertCount(2, $rows);
        $this->assertStringStartsWith('(', $rows[0]); // level 0: no tabs
        $this->assertStringStartsWith("\t(", $rows[1]); // level 1: one tab
    }

    public function test_diff_with_multiple_children_iterates_all(): void
    {
        // Build tree with 2 children, both with differences
        $cat1 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'root', 'lvl' => '0', 'lft' => '1', 'rgt' => '6',
        ]);
        $cat1->addChild(new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'a', 'lvl' => '1', 'lft' => '2', 'rgt' => '3',
        ]));
        $cat1->addChild(new Category([
            'id' => 3, 'parent_id' => 1, 'root' => 1,
            'code' => 'b', 'lvl' => '1', 'lft' => '4', 'rgt' => '5',
        ]));

        $cat2 = new Category([
            'id' => 1, 'parent_id' => null, 'root' => 1,
            'code' => 'root', 'lvl' => '0', 'lft' => '1', 'rgt' => '6',
        ]);
        $cat2->addChild(new Category([
            'id' => 2, 'parent_id' => 1, 'root' => 1,
            'code' => 'a', 'lvl' => '1', 'lft' => '2', 'rgt' => '99',
        ]));
        $cat2->addChild(new Category([
            'id' => 3, 'parent_id' => 1, 'root' => 1,
            'code' => 'b', 'lvl' => '1', 'lft' => '4', 'rgt' => '88',
        ]));

        $diffs = $cat1->diff($cat2);
        // Should have diffs from both children
        $this->assertGreaterThanOrEqual(2, count($diffs));
        $hasChild0 = false;
        $hasChild1 = false;
        foreach ($diffs as $diff) {
            if (str_contains($diff, 'Child at index 0:')) {
                $hasChild0 = true;
            }
            if (str_contains($diff, 'Child at index 1:')) {
                $hasChild1 = true;
            }
        }
        $this->assertTrue($hasChild0, 'Should have diff for child at index 0');
        $this->assertTrue($hasChild1, 'Should have diff for child at index 1');
    }

    private function getMasterCategory(): array
    {
        return [
            'id' => 1,
            'parent_id' => null,
            'root' => 1,
            'code' => 'master',
            'lvl' => '0',
            'lft' => '1',
            'rgt' => '8',
        ];
    }

    private function getCategories(): array
    {
        return [
            'child1' => new Category([
                'id' => 2,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child1',
                'lvl' => '1',
                'lft' => '2',
                'rgt' => '3',
            ]),
            'child2' => new Category([
                'id' => 3,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child2',
                'lvl' => '1',
                'lft' => '4',
                'rgt' => '5',
            ]),
            'child3' => new Category([
                'id' => 4,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child3',
                'lvl' => '1',
                'lft' => '6',
                'rgt' => '7',
            ]),
        ];
    }

    private function getDisorderedCategories(): array
    {
        return [
            'child1' => new Category([
                'id' => 2,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child1',
                'lvl' => '1',
                'lft' => '3',
                'rgt' => '2',
            ]),
            'child2' => new Category([
                'id' => 3,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child2',
                'lvl' => '1',
                'lft' => '4',
                'rgt' => '7',
            ]),
            'child3' => new Category([
                'id' => 4,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child3',
                'lvl' => '2',
                'lft' => '6',
                'rgt' => '7',
            ]),
            'child4' => new Category([
                'id' => 5,
                'parent_id' => 1,
                'root' => 1,
                'code' => 'child4',
                'lvl' => '1',
                'lft' => '6',
                'rgt' => '7',
            ]),
        ];
    }
}
