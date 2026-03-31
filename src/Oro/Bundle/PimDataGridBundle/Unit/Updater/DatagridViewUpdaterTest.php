<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Oro\Bundle\PimDataGridBundle\Updater\DatagridViewUpdater;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridViewUpdaterTest extends TestCase
{
    private IdentifiableObjectRepositoryInterface|MockObject $userRepository;
    private DatagridViewUpdater $sut;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(IdentifiableObjectRepositoryInterface::class);
        $this->sut = new DatagridViewUpdater($this->userRepository);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DatagridViewUpdater::class, $this->sut);
    }

    public function test_it_is_a_object_updater(): void
    {
        $this->assertInstanceOf(ObjectUpdaterInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_if_the_given_object_is_not_a_datagrid(): void
    {
        $this->expectException(InvalidObjectException::class);
        $this->expectExceptionMessage(sprintf('Expects a "%s", "%s" given.', DatagridView::class, 'stdClass'));
        $this->sut->update(new \stdClass(), []);
    }

    public function test_it_updates_the_data_grid_property(): void
    {
        $datagridView = $this->createMock(DatagridView::class);
        $user = $this->createMock(UserInterface::class);

        $this->userRepository->method('findOneByIdentifier')->with('julia')->willReturn($user);
        $datagridView->expects($this->once())->method('setLabel')->with('My view');
        $datagridView->expects($this->once())->method('setOwner')->with($user);
        $datagridView->expects($this->once())->method('setType')->with(DatagridView::TYPE_PUBLIC);
        $datagridView->expects($this->once())->method('setDatagridAlias')->with('product-grid');
        $datagridView->expects($this->once())->method('setColumns')->with(['name', 'price']);
        $datagridView->expects($this->once())->method('setFilters')->with('my filter as string');
        $this->assertSame($this->sut, $this->sut->update($datagridView, [
                    'owner' => 'julia',
                    'type' => DatagridView::TYPE_PUBLIC,
                    'datagrid_alias' => 'product-grid',
                    'label' => 'My view',
                    'columns' => 'name, price',
                    'filters' => 'my filter as string',
                ]));
    }
}
