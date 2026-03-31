<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\Datasource;
use Oro\Bundle\PimDataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatasourceTest extends TestCase
{
    private ObjectManager|MockObject $manager;
    private HydratorInterface|MockObject $hydrator;
    private Datasource $sut;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(ObjectManager::class);
        $this->hydrator = $this->createMock(HydratorInterface::class);
        $this->sut = new Datasource($this->manager, $this->hydrator);
    }

    public function test_it_is_a_datasource(): void
    {
        $this->assertInstanceOf(DatasourceInterface::class, $this->sut);
    }

    public function test_it_processes_a_datasource_with_repository_configuration(): void
    {
        $grid = $this->createMock(DatagridInterface::class);
        $repository = $this->createMock(GroupRepositoryInterface::class);

        $config = [
                    'repository_method' => 'createAssociationDatagridQueryBuilder',
                    'entity'            => 'Group',
                ];
        $this->manager->method('getRepository')->with('Group')->willReturn($repository);
        $repository->expects($this->once())->method('createAssociationDatagridQueryBuilder')->with([]);
        $grid->expects($this->once())->method('setDatasource')->with($this->sut);
        $this->sut->process($grid, $config);
    }

    public function test_it_processes_a_datasource_with_repository_configuration_and_parameters(): void
    {
        $grid = $this->createMock(DatagridInterface::class);
        $repository = $this->createMock(GroupRepositoryInterface::class);

        $config = [
                    'repository_method'     => 'createAssociationDatagridQueryBuilder',
                    'repository_parameters' => ['locale' => 'fr_FR'],
                    'entity'                => 'Group',
                ];
        $this->manager->method('getRepository')->with('Group')->willReturn($repository);
        $repository->expects($this->once())->method('createAssociationDatagridQueryBuilder')->with(['locale' => 'fr_FR']);
        $grid->expects($this->once())->method('setDatasource')->with($this->sut);
        $this->sut->process($grid, $config);
    }

    public function test_it_throws_exception_when_process_with_missing_configuration(): void
    {
        $grid = $this->createMock(DatagridInterface::class);

        $config = [
                    'repository_method' => 'createAssociationDatagridQueryBuilder',
                ];
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('"Oro\Bundle\PimDataGridBundle\Datasource\Datasource" expects to be configured with "entity"');
        $this->sut->process($grid, $config);
    }
}
