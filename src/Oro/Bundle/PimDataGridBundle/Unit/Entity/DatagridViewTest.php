<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Entity;

use Akeneo\UserManagement\Component\Model\User;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatagridViewTest extends TestCase
{
    private DatagridView $sut;

    protected function setUp(): void
    {
        $this->sut = new DatagridView();
    }

    public function test_it_stores_the_label_of_the_view(): void
    {
        $owner = $this->createMock(User::class);

        $this->sut->setLabel('random view');
        $this->assertSame('random view', $this->sut->getLabel());
    }

    public function test_it_stores_the_owner_of_the_view(): void
    {
        $owner = $this->createMock(User::class);

        $this->sut->setOwner($owner);
        $this->assertSame($owner, $this->sut->getOwner());
    }

    public function test_it_stores_the_datagrid_alias(): void
    {
        $this->sut->setDatagridAlias('foo-grid');
        $this->assertSame('foo-grid', $this->sut->getDatagridAlias());
    }

    public function test_it_stores_the_displayed_columns(): void
    {
        $this->sut->setColumns(['foo', 'bar', 'baz']);
        $this->assertSame(['foo', 'bar', 'baz'], $this->sut->getColumns());
    }

    public function test_it_stores_the_displayed_filters(): void
    {
        $this->sut->setFilters('sku=1');
        $this->assertSame('sku=1', $this->sut->getFilters());
    }
}
