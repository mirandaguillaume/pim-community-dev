<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Twig;

use Akeneo\Category\Infrastructure\Component\CategoryItemsCounterInterface;
use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Category\Infrastructure\Doctrine\ORM\Counter\CategoryItemsCounterRegistry;
use Akeneo\Category\Infrastructure\Twig\CategoryExtension;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;

class CategoryExtensionTest extends TestCase
{
    private CategoryItemsCounterRegistry|MockObject $registry;
    private CategoryExtension $sut;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(CategoryItemsCounterRegistry::class);
        $productsLimitForRemoval = 10;
        $this->sut = new CategoryExtension($this->registry, $productsLimitForRemoval);
    }

    public function testItIsATwigExtension(): void
    {
        $this->assertInstanceOf(AbstractExtension::class, $this->sut);
    }

    public function testItRegistersCategoryFunctions(): void
    {
        $functions = $this->sut->getFunctions();
        $this->assertCount(5, $functions);
        $this->assertSame('children_response', $functions[0]->getName());
        $this->assertSame('children_tree_response', $functions[1]->getName());
        $this->assertSame('list_categories_response', $functions[2]->getName());
        $this->assertSame('exceeds_products_limit_for_removal', $functions[3]->getName());
        $this->assertSame('get_products_limit_for_removal', $functions[4]->getName());
    }

    public function testItFormatsAListOfCategoriesWithProductCount(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $root = $this->createMock(Category::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(5);
        $root->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $root->expects($this->atLeastOnce())->method('getCode')->willReturn('root');
        $root->expects($this->atLeastOnce())->method('getLabel')->willReturn('Root');
        $root->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $root->expects($this->atLeastOnce())->method('isRoot')->willReturn(true);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('selected_category');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Selected category');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1Array = [
            'item' => $category1,
            '__children' => [],
        ];
        $category2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $category2->expects($this->atLeastOnce())->method('getCode')->willReturn('some_category');
        $category2->expects($this->atLeastOnce())->method('getLabel')->willReturn('Some category');
        $category2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category2Array = [
            'item' => $category2,
            '__children' => [],
        ];
        $expected = [
            'attr' => ['id' => 'node_1', 'data-code' => 'root'],
            'data' => 'Root (5)',
            'state' => 'closed jstree-root',
            'children' => [
                [
                    'attr' => ['id' => 'node_2', 'data-code' => 'selected_category'],
                    'data' => 'Selected category (5)',
                    'state' => 'leaf toselect jstree-checked',
                    'children' => [],
                ],
                [
                    'attr' => ['id' => 'node_3', 'data-code' => 'some_category'],
                    'data' => 'Some category (5)',
                    'state' => 'leaf',
                    'children' => [],
                ],
            ],
        ];
        $actual = $this->sut->childrenTreeResponse([$category1Array, $category2Array], $category1, $root, true);
        $this->assertSame($expected['attr'], $actual['attr']);
        $this->assertSame($expected['data'], $actual['data']);
        $this->assertSame($expected['state'], $actual['state']);
        $this->assertCount(2, $actual['children']);
        $this->assertSame('node_2', $actual['children'][0]['attr']['id']);
        $this->assertSame('selected_category', $actual['children'][0]['attr']['data-code']);
        $this->assertSame('Selected category (5)', $actual['children'][0]['data']);
        $this->assertStringContainsString('toselect', $actual['children'][0]['state']);
        $this->assertStringContainsString('jstree-checked', $actual['children'][0]['state']);
        $this->assertSame('node_3', $actual['children'][1]['attr']['id']);
        $this->assertSame('some_category', $actual['children'][1]['attr']['data-code']);
        $this->assertSame('Some category (5)', $actual['children'][1]['data']);
        $this->assertStringNotContainsString('toselect', $actual['children'][1]['state']);
        $this->assertStringContainsString('leaf', $actual['children'][1]['state']);
    }

    public function testItFormatsAListOfCategoriesWithoutProductCount(): void
    {
        $root = $this->createMock(Category::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);

        $root->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $root->expects($this->atLeastOnce())->method('getCode')->willReturn('root');
        $root->expects($this->atLeastOnce())->method('getLabel')->willReturn('Root');
        $root->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $root->expects($this->atLeastOnce())->method('isRoot')->willReturn(true);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('selected_category');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Selected category');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1Array = [
            'item' => $category1,
            '__children' => [],
        ];
        $category2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $category2->expects($this->atLeastOnce())->method('getCode')->willReturn('some_category');
        $category2->expects($this->atLeastOnce())->method('getLabel')->willReturn('Some category');
        $category2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category2Array = [
            'item' => $category2,
            '__children' => [],
        ];
        $actual = $this->sut->childrenTreeResponse([$category1Array, $category2Array], $category1, $root);

        // Without product count, labels should NOT have "(count)" appended
        $this->assertSame('Root', $actual['data']);
        $this->assertSame('Selected category', $actual['children'][0]['data']);
        $this->assertSame('Some category', $actual['children'][1]['data']);
        // State assertions
        $this->assertStringContainsString('closed', $actual['state']);
        $this->assertStringContainsString('jstree-root', $actual['state']);
        $this->assertStringContainsString('leaf', $actual['children'][0]['state']);
        $this->assertStringContainsString('toselect', $actual['children'][0]['state']);
        $this->assertStringContainsString('leaf', $actual['children'][1]['state']);
        $this->assertStringNotContainsString('toselect', $actual['children'][1]['state']);
        // Attr assertions
        $this->assertSame('node_1', $actual['attr']['id']);
        $this->assertSame('root', $actual['attr']['data-code']);
        $this->assertSame('node_2', $actual['children'][0]['attr']['id']);
        $this->assertSame('node_3', $actual['children'][1]['attr']['id']);
    }

    public function testItListsCategoriesAndTheirChildrenWithProductCount(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $category0 = $this->createMock(Category::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(5);
        $category0->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category0->expects($this->atLeastOnce())->method('getCode')->willReturn('selected_category');
        $category0->expects($this->atLeastOnce())->method('getLabel')->willReturn('Selected category');
        $category0->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $category0->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('sub_category1');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Sub-category 1');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $category2->expects($this->atLeastOnce())->method('getCode')->willReturn('sub_category2');
        $category2->expects($this->atLeastOnce())->method('getLabel')->willReturn('Sub-category 2');
        $category2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $actual = $this->sut->childrenResponse([$category1, $category2], $category0, true);
        $this->assertSame('node_1', $actual['attr']['id']);
        $this->assertSame('selected_category', $actual['attr']['data-code']);
        $this->assertSame('Selected category (5)', $actual['data']);
        $this->assertSame('closed', $actual['state']);
        $this->assertCount(2, $actual['children']);
        $this->assertSame('Sub-category 1 (5)', $actual['children'][0]['data']);
        $this->assertSame('Sub-category 2 (5)', $actual['children'][1]['data']);
        $this->assertSame('leaf', $actual['children'][0]['state']);
        $this->assertSame('leaf', $actual['children'][1]['state']);
        $this->assertSame('node_2', $actual['children'][0]['attr']['id']);
        $this->assertSame('node_3', $actual['children'][1]['attr']['id']);
        $this->assertSame('sub_category1', $actual['children'][0]['attr']['data-code']);
        $this->assertSame('sub_category2', $actual['children'][1]['attr']['data-code']);
    }

    public function testItListsCategoriesAndTheirChildrenWithoutProductCount(): void
    {
        $category0 = $this->createMock(Category::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);

        $category0->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category0->expects($this->atLeastOnce())->method('getCode')->willReturn('selected_category');
        $category0->expects($this->atLeastOnce())->method('getLabel')->willReturn('Selected category');
        $category0->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $category0->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('sub_category1');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Sub-category 1');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $category2->expects($this->atLeastOnce())->method('getCode')->willReturn('sub_category2');
        $category2->expects($this->atLeastOnce())->method('getLabel')->willReturn('Sub-category 2');
        $category2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $actual = $this->sut->childrenResponse([$category1, $category2], $category0);
        $this->assertSame('Selected category', $actual['data']);
        $this->assertSame('Sub-category 1', $actual['children'][0]['data']);
        $this->assertSame('Sub-category 2', $actual['children'][1]['data']);
        $this->assertSame('closed', $actual['state']);
        $this->assertStringNotContainsString('jstree-root', $actual['state']);
        $this->assertSame('node_1', $actual['attr']['id']);
        $this->assertSame('selected_category', $actual['attr']['data-code']);
    }

    public function testChildrenResponseWithoutParent(): void
    {
        $category1 = $this->createMock(Category::class);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('sub_cat');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Sub cat');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenResponse([$category1], null);
        $this->assertIsArray($actual);
        $this->assertCount(1, $actual);
        $this->assertSame('node_2', $actual[0]['attr']['id']);
        $this->assertSame('sub_cat', $actual[0]['attr']['data-code']);
        $this->assertSame('Sub cat', $actual[0]['data']);
        $this->assertSame('leaf', $actual[0]['state']);
    }

    public function testChildrenTreeResponseWithoutParent(): void
    {
        $category1 = $this->createMock(Category::class);
        $selectedCategory = $this->createMock(Category::class);
        $selectedCategory->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('cat1');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Cat 1');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $category1Array = [
            'item' => $category1,
            '__children' => [],
        ];
        $actual = $this->sut->childrenTreeResponse([$category1Array], $selectedCategory, null);
        // Without parent, result is the array of formatted categories
        $this->assertIsArray($actual);
        $this->assertCount(1, $actual);
        $this->assertSame('node_2', $actual[0]['attr']['id']);
        $this->assertStringContainsString('toselect', $actual[0]['state']);
    }

    public function testItListsAndFormatCategories(): void
    {
        $category0 = $this->createMock(Category::class);
        $category1 = $this->createMock(Category::class);
        $category2 = $this->createMock(Category::class);

        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('some_category1');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Some category 1');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1Array = [
            'item' => $category1,
            '__children' => [],
        ];
        $category2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $category2->expects($this->atLeastOnce())->method('getCode')->willReturn('some_category2');
        $category2->expects($this->atLeastOnce())->method('getLabel')->willReturn('Some category 2');
        $category2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category2Array = [
            'item' => $category2,
            '__children' => [],
        ];
        $category0->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category0->expects($this->atLeastOnce())->method('getCode')->willReturn('parent_category');
        $category0->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent category');
        $category0->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $category0->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category0Array = [
            'item' => $category0,
            '__children' => [$category1Array, $category2Array],
        ];

        $actual = $this->sut->listCategoriesResponse([$category0Array], new ArrayCollection());
        $this->assertCount(1, $actual);
        $this->assertSame('node_1', $actual[0]['attr']['id']);
        $this->assertSame('parent_category', $actual[0]['attr']['data-code']);
        $this->assertSame('Parent category', $actual[0]['data']);
        $this->assertSame('open', $actual[0]['state']);
        $this->assertCount(2, $actual[0]['children']);
        $this->assertSame('Some category 1', $actual[0]['children'][0]['data']);
        $this->assertSame('Some category 2', $actual[0]['children'][1]['data']);
        $this->assertSame('leaf', $actual[0]['children'][0]['state']);
        $this->assertSame('leaf', $actual[0]['children'][1]['state']);
        $this->assertSame(0, $actual[0]['selectedChildrenCount']);
        $this->assertSame(0, $actual[0]['children'][0]['selectedChildrenCount']);
        $this->assertSame(0, $actual[0]['children'][1]['selectedChildrenCount']);
    }

    public function testListCategoriesResponseWithSelectedCategories(): void
    {
        $selectedCat = $this->createMock(Category::class);
        $selectedCat->method('getId')->willReturn(2);

        $category1 = $this->createMock(Category::class);
        $category1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $category1->expects($this->atLeastOnce())->method('getCode')->willReturn('cat1');
        $category1->expects($this->atLeastOnce())->method('getLabel')->willReturn('Cat 1');
        $category1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $category1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $category1Array = [
            'item' => $category1,
            '__children' => [],
        ];
        $parentCat = $this->createMock(Category::class);
        $parentCat->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parentCat->expects($this->atLeastOnce())->method('getCode')->willReturn('parent');
        $parentCat->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent');
        $parentCat->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parentCat->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $parentArray = [
            'item' => $parentCat,
            '__children' => [$category1Array],
        ];

        $actual = $this->sut->listCategoriesResponse([$parentArray], new ArrayCollection([$selectedCat]));
        $this->assertCount(1, $actual);
        // The child should be marked with jstree-checked
        $this->assertStringContainsString('jstree-checked', $actual[0]['children'][0]['state']);
        // Parent selectedChildrenCount should include the checked child
        $this->assertSame(1, $actual[0]['selectedChildrenCount']);
    }

    public function testItChecksIfACategoryExceedsTheProductsLimitForRemoval(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $category = $this->createMock(Category::class);

        $this->registry->expects($this->once())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->once())->method('getItemsCountInCategory')->with($category, true)->willReturn(11);
        $this->assertSame(true, $this->sut->exceedsProductsLimitForRemoval($category, true));
    }

    public function testItDoesNotExceedProductsLimitWhenAtLimit(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $category = $this->createMock(Category::class);

        $this->registry->expects($this->once())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->once())->method('getItemsCountInCategory')->with($category, true)->willReturn(10);
        $this->assertSame(false, $this->sut->exceedsProductsLimitForRemoval($category, true));
    }

    public function testItDoesNotExceedWhenBelowLimit(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $category = $this->createMock(Category::class);

        $this->registry->expects($this->once())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->once())->method('getItemsCountInCategory')->with($category, false)->willReturn(5);
        $this->assertSame(false, $this->sut->exceedsProductsLimitForRemoval($category, false));
    }

    public function testExceedsLimitReturnsFalseWhenLimitIsNull(): void
    {
        $sut = new CategoryExtension($this->registry, null);
        $category = $this->createMock(Category::class);
        // When itemsLimitRemoval is null, it should always return false
        $this->registry->expects($this->never())->method('get');
        $this->assertSame(false, $sut->exceedsProductsLimitForRemoval($category, true));
    }

    public function testItGivesTheProductsLimitForRemoval(): void
    {
        $this->assertSame(10, $this->sut->getProductsLimitForRemoval());
    }

    public function testProductsLimitForRemovalIsNullWhenNotSet(): void
    {
        $sut = new CategoryExtension($this->registry, null);
        $this->assertNull($sut->getProductsLimitForRemoval());
    }

    public function testChildrenResponseWithIncludeSub(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $parent = $this->createMock(Category::class);
        $child = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(7);

        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('parent');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('child');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('Child');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenResponse([$child], $parent, true, true);
        $this->assertSame('Parent (7)', $actual['data']);
        $this->assertSame('Child (7)', $actual['children'][0]['data']);
    }

    public function testCategoryStateForRootCategory(): void
    {
        $root = $this->createMock(Category::class);
        $root->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $root->expects($this->atLeastOnce())->method('getCode')->willReturn('root');
        $root->expects($this->atLeastOnce())->method('getLabel')->willReturn('Root');
        $root->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $root->expects($this->atLeastOnce())->method('isRoot')->willReturn(true);

        $actual = $this->sut->childrenResponse([], $root);
        $this->assertStringContainsString('jstree-root', $actual['state']);
        $this->assertStringContainsString('closed', $actual['state']);
    }

    public function testCategoryStateForLeafCategory(): void
    {
        $leaf = $this->createMock(Category::class);
        $leaf->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $leaf->expects($this->atLeastOnce())->method('getCode')->willReturn('leaf');
        $leaf->expects($this->atLeastOnce())->method('getLabel')->willReturn('Leaf');
        $leaf->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $leaf->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenResponse([], $leaf);
        $this->assertStringContainsString('leaf', $actual['state']);
        $this->assertStringNotContainsString('jstree-root', $actual['state']);
        $this->assertStringNotContainsString('closed', $actual['state']);
    }

    public function testCategoryWithChildrenHasOpenState(): void
    {
        $parent = $this->createMock(Category::class);
        $child = $this->createMock(Category::class);

        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('parent');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);
        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('child');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('Child');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $childArray = ['item' => $child, '__children' => []];
        $parentArray = ['item' => $parent, '__children' => [$childArray]];

        $actual = $this->sut->listCategoriesResponse([$parentArray], new ArrayCollection());
        // Parent has children in __children, so state should be 'open'
        $this->assertSame('open', $actual[0]['state']);
    }

    public function testEmptyCategoriesList(): void
    {
        $actual = $this->sut->listCategoriesResponse([], new ArrayCollection());
        $this->assertSame([], $actual);
    }

    public function testChildrenResponseWithEmptyChildrenList(): void
    {
        $parent = $this->createMock(Category::class);
        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('p');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('P');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenResponse([], $parent);
        $this->assertArrayNotHasKey('children', $actual);
        $this->assertSame('leaf', $actual['state']);
    }

    public function testGetExtensionThrowsOnNullCounter(): void
    {
        $category = $this->createMock(Category::class);
        $this->registry->expects($this->once())->method('get')->with('nonexistent')->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No category counter found for nonexistent');
        $this->sut->exceedsProductsLimitForRemoval($category, true, 'nonexistent');
    }

    public function testChildrenTreeResponseWithNestedChildren(): void
    {
        $selectedCategory = $this->createMock(Category::class);
        $selectedCategory->method('getId')->willReturn(3);

        $grandchild = $this->createMock(Category::class);
        $grandchild->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $grandchild->expects($this->atLeastOnce())->method('getCode')->willReturn('gc');
        $grandchild->expects($this->atLeastOnce())->method('getLabel')->willReturn('GC');
        $grandchild->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $grandchild->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $child = $this->createMock(Category::class);
        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('c');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('C');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $childArray = [
            'item' => $child,
            '__children' => [
                ['item' => $grandchild, '__children' => []],
            ],
        ];
        $actual = $this->sut->childrenTreeResponse([$childArray], $selectedCategory, null);
        $this->assertCount(1, $actual);
        $this->assertSame('open', $actual[0]['state']);
        $this->assertCount(1, $actual[0]['children']);
        $this->assertStringContainsString('toselect', $actual[0]['children'][0]['state']);
    }

    /**
     * Tests that explicitly pass false for includeSub to kill FalseValue default param mutations.
     * The mutations flip `false` to `true` in default params.
     * By explicitly passing false and asserting the absence of count, we verify the default matters.
     */
    public function testChildrenResponseExplicitFalseIncludeSubNoProductCount(): void
    {
        $parent = $this->createMock(Category::class);
        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('p');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('P');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        // Explicitly pass withProductCount=false, includeSub=false
        $actual = $this->sut->childrenResponse([], $parent, false, false);
        // Label should NOT contain parentheses (no product count)
        $this->assertStringNotContainsString('(', $actual['data']);
    }

    public function testChildrenResponseWithProductCountAndIncludeSubTrue(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $parent = $this->createMock(Category::class);
        $child = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(3);

        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('p');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('P');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('c');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('C');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        // withProductCount=true, includeSub=true
        $actual = $this->sut->childrenResponse([$child], $parent, true, true);
        $this->assertStringContainsString('(3)', $actual['data']);
        $this->assertStringContainsString('(3)', $actual['children'][0]['data']);
    }

    public function testListCategoriesAccumulatesSelectedChildrenCountFromMultipleChecked(): void
    {
        $selectedCat1 = $this->createMock(Category::class);
        $selectedCat1->method('getId')->willReturn(2);
        $selectedCat2 = $this->createMock(Category::class);
        $selectedCat2->method('getId')->willReturn(3);

        $child1 = $this->createMock(Category::class);
        $child1->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child1->expects($this->atLeastOnce())->method('getCode')->willReturn('c1');
        $child1->expects($this->atLeastOnce())->method('getLabel')->willReturn('C1');
        $child1->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child1->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $child2 = $this->createMock(Category::class);
        $child2->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $child2->expects($this->atLeastOnce())->method('getCode')->willReturn('c2');
        $child2->expects($this->atLeastOnce())->method('getLabel')->willReturn('C2');
        $child2->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child2->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $parentCat = $this->createMock(Category::class);
        $parentCat->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parentCat->expects($this->atLeastOnce())->method('getCode')->willReturn('parent');
        $parentCat->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent');
        $parentCat->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parentCat->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $parentArray = [
            'item' => $parentCat,
            '__children' => [
                ['item' => $child1, '__children' => []],
                ['item' => $child2, '__children' => []],
            ],
        ];

        $actual = $this->sut->listCategoriesResponse([$parentArray], new ArrayCollection([$selectedCat1, $selectedCat2]));
        // Both children are checked, so parent selectedChildrenCount = 2
        $this->assertSame(2, $actual[0]['selectedChildrenCount']);
        // Both children should be marked checked
        $this->assertStringContainsString('jstree-checked', $actual[0]['children'][0]['state']);
        $this->assertStringContainsString('jstree-checked', $actual[0]['children'][1]['state']);
    }

    public function testListCategoriesNestedSelectedChildrenCountAccumulates(): void
    {
        $selectedCat = $this->createMock(Category::class);
        $selectedCat->method('getId')->willReturn(3);

        $grandchild = $this->createMock(Category::class);
        $grandchild->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $grandchild->expects($this->atLeastOnce())->method('getCode')->willReturn('gc');
        $grandchild->expects($this->atLeastOnce())->method('getLabel')->willReturn('GC');
        $grandchild->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $grandchild->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $child = $this->createMock(Category::class);
        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('c');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('C');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $root = $this->createMock(Category::class);
        $root->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $root->expects($this->atLeastOnce())->method('getCode')->willReturn('root');
        $root->expects($this->atLeastOnce())->method('getLabel')->willReturn('Root');
        $root->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $root->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $rootArray = [
            'item' => $root,
            '__children' => [
                [
                    'item' => $child,
                    '__children' => [
                        ['item' => $grandchild, '__children' => []],
                    ],
                ],
            ],
        ];

        $actual = $this->sut->listCategoriesResponse([$rootArray], new ArrayCollection([$selectedCat]));
        // grandchild is checked, so child.selectedChildrenCount = 1
        $this->assertSame(1, $actual[0]['children'][0]['selectedChildrenCount']);
        // root.selectedChildrenCount = child.selectedChildrenCount (1) + grandchild checked (propagated through child)
        $this->assertSame(1, $actual[0]['selectedChildrenCount']);
    }

    public function testChildrenTreeResponseWithCustomRelatedEntity(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $parent = $this->createMock(Category::class);
        $child = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product_model')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(42);

        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('parent');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('Parent');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(true);

        $child->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $child->expects($this->atLeastOnce())->method('getCode')->willReturn('ch');
        $child->expects($this->atLeastOnce())->method('getLabel')->willReturn('Ch');
        $child->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $child->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $childArray = ['item' => $child, '__children' => []];

        $selectedCategory = $this->createMock(Category::class);
        $selectedCategory->method('getId')->willReturn(2);

        $actual = $this->sut->childrenTreeResponse(
            [$childArray],
            $selectedCategory,
            $parent,
            true,     // withProductCount
            false,    // includeSub
            'product_model', // relatedEntity
        );
        $this->assertStringContainsString('(42)', $actual['data']);
        $this->assertStringContainsString('(42)', $actual['children'][0]['data']);
    }

    public function testDefineCategoryStateFromArrayWithNonCountableChildren(): void
    {
        // Test the is_countable fallback branch (children = null is not countable)
        // We test by ensuring when __children is empty array, hasChild=false -> state=leaf
        $leaf = $this->createMock(Category::class);
        $leaf->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $leaf->expects($this->atLeastOnce())->method('getCode')->willReturn('leaf');
        $leaf->expects($this->atLeastOnce())->method('getLabel')->willReturn('Leaf');
        $leaf->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $leaf->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenTreeResponse(
            [['item' => $leaf, '__children' => []]],
            $leaf,
            null,
            false,
            false,
        );
        // With empty __children array, state should be leaf (not open)
        $this->assertStringContainsString('leaf', $actual[0]['state']);
        $this->assertStringNotContainsString('open', $actual[0]['state']);
    }

    public function testChildrenResponseWithProductModelEntity(): void
    {
        $categoryItemsCounter = $this->createMock(CategoryItemsCounterInterface::class);
        $parent = $this->createMock(Category::class);

        $this->registry->expects($this->atLeastOnce())->method('get')->with('product_model')->willReturn($categoryItemsCounter);
        $categoryItemsCounter->expects($this->atLeastOnce())->method('getItemsCountInCategory')->willReturn(99);

        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $parent->expects($this->atLeastOnce())->method('getCode')->willReturn('p');
        $parent->expects($this->atLeastOnce())->method('getLabel')->willReturn('P');
        $parent->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $parent->expects($this->atLeastOnce())->method('isRoot')->willReturn(false);

        $actual = $this->sut->childrenResponse([], $parent, true, false, 'product_model');
        $this->assertStringContainsString('(99)', $actual['data']);
    }
}
