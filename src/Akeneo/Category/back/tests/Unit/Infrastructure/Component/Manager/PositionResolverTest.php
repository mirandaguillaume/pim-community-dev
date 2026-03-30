<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Manager;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolver;
use Akeneo\Category\Infrastructure\Component\Manager\PositionResolverInterface;
use Akeneo\Pim\Enrichment\Component\Category\Query\GetDirectChildrenCategoryCodesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PositionResolverTest extends TestCase
{
    private GetDirectChildrenCategoryCodesInterface|MockObject $getDirectChildrenCategoryCodes;
    private PositionResolver $sut;

    protected function setUp(): void
    {
        $this->getDirectChildrenCategoryCodes = $this->createMock(GetDirectChildrenCategoryCodesInterface::class);
        $this->sut = new PositionResolver($this->getDirectChildrenCategoryCodes);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(PositionResolverInterface::class, $this->sut);
        $this->assertInstanceOf(PositionResolver::class, $this->sut);
    }

    public function testItGetsPositionWhenCategoryHasNoParent(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $category->method('isRoot')->willReturn(true);
        $this->assertSame(1, $this->sut->getPosition($category));
    }

    public function testItGetsPosition(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryParent = $this->createMock(CategoryInterface::class);

        $aCategoryCode = 'categoryC';
        $aCategoryParentId = 1;
        $aListOfParentCategoryChildren = [
            'categoryA' => ['row_num' => 1],
            'categoryB' => ['row_num' => 2],
            'categoryC' => ['row_num' => 3],
        ];
        $category->method('getCode')->willReturn($aCategoryCode);
        $category->method('isRoot')->willReturn(false);
        $category->method('getParent')->willReturn($categoryParent);
        $categoryParent->method('getId')->willReturn($aCategoryParentId);
        $this->getDirectChildrenCategoryCodes->method('execute')->with($aCategoryParentId)->willReturn($aListOfParentCategoryChildren);
        $this->assertSame(3, $this->sut->getPosition($category));
    }
}
