<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Application\Match\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Match\Condition\MatchCategoryHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Category;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\CategoriesHaveAtLeastOneChild;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MatchCategoryHandlerTest extends TestCase
{
    private CategoriesHaveAtLeastOneChild|MockObject $categoriesHaveAtLeastOneChild;
    private MatchCategoryHandler $sut;

    protected function setUp(): void
    {
        $this->categoriesHaveAtLeastOneChild = $this->createMock(CategoriesHaveAtLeastOneChild::class);
        $this->sut = new MatchCategoryHandler($this->categoriesHaveAtLeastOneChild);
    }

    public function test_it_should_support_only_category_conditions(): void
    {
        $this->assertSame(Category::class, $this->sut->getConditionClass());
    }

    public function test_it_should_throw_exception_when_invoked_with_something_else_than_category_condition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->__invoke(
            Enabled::fromBoolean(true),
            new ProductProjection(true, null, [], []),
        );
    }

    public function test_it_should_match_classified(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'CLASSIFIED',
        ]);
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes'])));
    }

    public function test_it_should_match_unclassified(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'UNCLASSIFIED',
        ]);
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes'])));
    }

    public function test_it_should_match_in_list(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'IN',
            'value' => ['shoes'],
        ]);
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes'])));
    }

    public function test_it_should_match_not_in_list(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'NOT IN',
            'value' => ['shoes'],
        ]);
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants', 'shoes'])));
    }

    public function test_it_should_match_in_children(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'IN CHILDREN',
            'value' => ['shoes'],
        ]);
        $this->categoriesHaveAtLeastOneChild->method('among')->willReturnCallback(
            function (array $parentCodes, array $productCategories): bool {
                if ($productCategories === []) {
                    return false;
                }
                if ($productCategories === ['blue_shoes']) {
                    return true;
                }
                if ($productCategories === ['pants']) {
                    return false;
                }

                return false;
            }
        );
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['blue_shoes'])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
    }

    public function test_it_should_match_not_in_children(): void
    {
        $condition = Category::fromNormalized([
            'type' => 'category',
            'operator' => 'NOT IN CHILDREN',
            'value' => ['shoes'],
        ]);
        $this->categoriesHaveAtLeastOneChild->method('among')->willReturnCallback(
            function (array $parentCodes, array $productCategories): bool {
                if ($productCategories === []) {
                    return false;
                }
                if ($productCategories === ['blue_shoes']) {
                    return true;
                }
                if ($productCategories === ['pants']) {
                    return false;
                }

                return false;
            }
        );
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], [])));
        $this->assertSame(false, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['blue_shoes'])));
        $this->assertSame(true, $this->sut->__invoke($condition, new ProductProjection(true, null, [], ['pants'])));
    }
}
