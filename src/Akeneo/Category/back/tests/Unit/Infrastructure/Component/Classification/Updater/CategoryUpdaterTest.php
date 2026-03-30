<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Classification\Updater;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Updater\CategoryUpdater;
use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\IsCategoryTreeLinkedToChannel;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Query\PublicApi\IsCategoryTreeLinkedToUser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryUpdaterTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $categoryRepository;
    private IsCategoryTreeLinkedToUser|MockObject $isCategoryTreeLinkedToUser;
    private IsCategoryTreeLinkedToChannel|MockObject $isCategoryTreeLinkedToChannel;
    private CategoryUpdater $sut;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->isCategoryTreeLinkedToUser = $this->createMock(IsCategoryTreeLinkedToUser::class);
        $this->isCategoryTreeLinkedToChannel = $this->createMock(IsCategoryTreeLinkedToChannel::class);
        $this->sut = new CategoryUpdater(
            $this->categoryRepository,
            $this->isCategoryTreeLinkedToUser,
            $this->isCategoryTreeLinkedToChannel,
        );
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(CategoryUpdater::class, $this->sut);
    }

    public function testItIsAUpdater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function testItThrowsAnExceptionWhenTryingToUpdateAnythingElseThanACategory(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->sut->update(new \stdClass(), []);
    }

    public function testItUpdatesANotTranslatableCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->expects($this->once())->method('setCode')->with('mycode');
        $category->expects($this->once())->method('setParent')->with($categoryMaster);
        $category->method('getId')->willReturn(null);
        $values = [
            'code' => 'mycode',
            'parent' => 'master',
        ];
        $result = $this->sut->update($category, $values, []);
        $this->assertInstanceOf(CategoryUpdater::class, $result);
    }

    public function testItUpdatesANullParentCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->once())->method('setCode')->with('mycode');
        $category->expects($this->once())->method('setParent')->with(null);
        $this->categoryRepository->expects($this->never())->method('findOneByIdentifier');
        $values = [
            'code' => 'mycode',
            'parent' => null,
        ];
        $result = $this->sut->update($category, $values, []);
        $this->assertInstanceOf(CategoryUpdater::class, $result);
    }

    public function testItUpdatesAnEmptyParentCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->once())->method('setParent')->with(null);
        $this->categoryRepository->expects($this->never())->method('findOneByIdentifier');
        $values = [
            'parent' => '',
        ];
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenTryingToUpdateANonExistentField(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'non_existent_field' => 'field',
        ];
        $this->expectException(UnknownPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenTryingToUpdateAnUnknownParentCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('unknown')->willReturn(null);
        $values = [
            'parent' => 'unknown',
        ];
        $this->expectException(InvalidPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenMovingARootCategoryStillLinkedToAUser(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category->expects($this->once())->method('isRoot')->willReturn(true);
        $this->isCategoryTreeLinkedToUser->expects($this->once())->method('byCategoryTreeId')->with(1)->willReturn(true);
        $this->isCategoryTreeLinkedToChannel->expects($this->never())->method('byCategoryTreeId');
        $values = [
            'parent' => 'master',
        ];
        $this->expectException(InvalidPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenMovingARootCategoryStillLinkedToAChannel(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category->expects($this->once())->method('isRoot')->willReturn(true);
        $this->isCategoryTreeLinkedToUser->expects($this->once())->method('byCategoryTreeId')->with(1)->willReturn(false);
        $this->isCategoryTreeLinkedToChannel->expects($this->once())->method('byCategoryTreeId')->with(1)->willReturn(true);
        $values = [
            'parent' => 'master',
        ];
        $this->expectException(InvalidPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItMovesARootCategoryNotLinkedToUserOrChannel(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $category->expects($this->once())->method('isRoot')->willReturn(true);
        $this->isCategoryTreeLinkedToUser->expects($this->once())->method('byCategoryTreeId')->with(1)->willReturn(false);
        $this->isCategoryTreeLinkedToChannel->expects($this->once())->method('byCategoryTreeId')->with(1)->willReturn(false);
        $category->expects($this->once())->method('setParent')->with($categoryMaster);
        $values = [
            'parent' => 'master',
        ];
        $this->sut->update($category, $values, []);
    }

    public function testItDoesNotCheckLinksForNonRootCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->method('getId')->willReturn(1);
        $category->expects($this->once())->method('isRoot')->willReturn(false);
        $this->isCategoryTreeLinkedToUser->expects($this->never())->method('byCategoryTreeId');
        $this->isCategoryTreeLinkedToChannel->expects($this->never())->method('byCategoryTreeId');
        $category->expects($this->once())->method('setParent')->with($categoryMaster);
        $values = [
            'parent' => 'master',
        ];
        $this->sut->update($category, $values, []);
    }

    public function testItDoesNotCheckLinksForNewCategory(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->method('getId')->willReturn(null);
        $this->isCategoryTreeLinkedToUser->expects($this->never())->method('byCategoryTreeId');
        $this->isCategoryTreeLinkedToChannel->expects($this->never())->method('byCategoryTreeId');
        $category->expects($this->once())->method('setParent')->with($categoryMaster);
        $values = [
            'parent' => 'master',
        ];
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenCodeIsNotAScalar(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'code' => [],
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenParentIsNotAScalar(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'parent' => [],
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenLabelsIsNotAnArray(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'labels' => 'foo',
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenOneOfTheLabelsInLabelPropertyIsNotAScalar(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'labels' => [
                'en_US' => 'foo',
                'fr_FR' => [],
            ],
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function testItThrowsAnExceptionWhenAPropertyIsUnknown(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'unknown' => 'foo',
        ];
        $this->expectException(UnknownPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function testUpdateReturnsSelf(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $result = $this->sut->update($category, []);
        $this->assertSame($this->sut, $result);
    }

}
