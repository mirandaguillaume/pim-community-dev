<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Component\Classification\Updater;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Updater\CategoryUpdater;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslation;
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
            $this->isCategoryTreeLinkedToChannel
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CategoryUpdater::class, $this->sut);
    }

    public function test_it_is_a_updater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_when_trying_to_update_anything_else_than_a_category(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->sut->update(new \stdClass(), []);
    }

    public function test_it_updates_a_not_translatable_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $categoryMaster = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('master')->willReturn($categoryMaster);
        $category->expects($this->once())->method('setCode')->with('mycode');
        $category->expects($this->once())->method('setParent')->with($categoryMaster);
        $category->method('getId')->willReturn(null);
        $values = [
            'code'         => 'mycode',
            'parent'       => 'master',
        ];
        $result = $this->sut->update($category, $values, []);
        $this->assertInstanceOf(CategoryUpdater::class, $result);
    }

    public function test_it_updates_a_null_parent_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->once())->method('setCode')->with('mycode');
        $category->expects($this->once())->method('setParent')->with(null);
        $this->categoryRepository->expects($this->never())->method('findOneByIdentifier');
        $values = [
            'code'   => 'mycode',
            'parent' => null,
        ];
        $result = $this->sut->update($category, $values, []);
        $this->assertInstanceOf(CategoryUpdater::class, $result);
    }

    public function test_it_updates_an_empty_parent_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $category->expects($this->once())->method('setParent')->with(null);
        $this->categoryRepository->expects($this->never())->method('findOneByIdentifier');
        $values = [
            'parent' => '',
        ];
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_trying_to_update_a_non_existent_field(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'non_existent_field' => 'field',
        ];
        $this->expectException(UnknownPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_trying_to_update_an_unknown_parent_category(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $this->categoryRepository->expects($this->once())->method('findOneByIdentifier')->with('unknown')->willReturn(null);
        $values = [
            'parent' => 'unknown',
        ];
        $this->expectException(InvalidPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_moving_a_root_category_still_linked_to_a_user(): void
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

    public function test_it_throws_an_exception_when_moving_a_root_category_still_linked_to_a_channel(): void
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

    public function test_it_moves_a_root_category_not_linked_to_user_or_channel(): void
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

    public function test_it_does_not_check_links_for_non_root_category(): void
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

    public function test_it_does_not_check_links_for_new_category(): void
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

    public function test_it_throws_an_exception_when_code_is_not_a_scalar(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'code' => [],
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_parent_is_not_a_scalar(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'parent' => [],
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_labels_is_not_an_array(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'labels' => 'foo',
        ];
        $this->expectException(InvalidPropertyTypeException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_it_throws_an_exception_when_one_of_the_labels_in_label_property_is_not_a_scalar(): void
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

    public function test_it_throws_an_exception_when_a_property_is_unknown(): void
    {
        $category = $this->createMock(CategoryInterface::class);

        $values = [
            'unknown' => 'foo',
        ];
        $this->expectException(UnknownPropertyException::class);
        $this->sut->update($category, $values, []);
    }

    public function test_update_returns_self(): void
    {
        $category = $this->createMock(CategoryInterface::class);
        $result = $this->sut->update($category, []);
        $this->assertSame($this->sut, $result);
    }
}
