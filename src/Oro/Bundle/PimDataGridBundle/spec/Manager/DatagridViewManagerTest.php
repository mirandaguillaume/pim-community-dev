<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Manager;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridViewManagerTest extends TestCase
{
    private EntityRepository|MockObject $repository;
    private Manager|MockObject $manager;
    private DatagridViewManager $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);
        $this->manager = $this->createMock(Manager::class);
        $this->sut = new DatagridViewManager($this->repository, $this->manager);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(DatagridViewManager::class, $this->sut);
    }
}
