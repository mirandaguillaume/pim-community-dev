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

    public function test_it_has_a_null_id_by_default(): void
    {
        $this->assertNull($this->sut->getId());
    }

    public function test_it_stores_the_type(): void
    {
        $this->assertSame($this->sut, $this->sut->setType(DatagridView::TYPE_PUBLIC));
        $this->assertSame(DatagridView::TYPE_PUBLIC, $this->sut->getType());
    }

    public function test_it_determines_if_view_is_public(): void
    {
        $this->sut->setType(DatagridView::TYPE_PUBLIC);
        $this->assertTrue($this->sut->isPublic());

        $this->sut->setType(DatagridView::TYPE_PRIVATE);
        $this->assertFalse($this->sut->isPublic());
    }

    public function test_setters_return_fluent_interface(): void
    {
        $owner = $this->createMock(User::class);
        $this->assertSame($this->sut, $this->sut->setLabel('test'));
        $this->assertSame($this->sut, $this->sut->setOwner($owner));
        $this->assertSame($this->sut, $this->sut->setDatagridAlias('alias'));
        $this->assertSame($this->sut, $this->sut->setColumns([]));
        $this->assertSame($this->sut, $this->sut->setFilters(''));
        $this->assertSame($this->sut, $this->sut->setOrder(''));
    }

    public function test_it_stores_and_retrieves_column_order(): void
    {
        $this->sut->setOrder('foo,bar,baz');
        $this->assertSame('foo,bar,baz', $this->sut->getOrder());
        $this->assertSame(['foo', 'bar', 'baz'], $this->sut->getColumns());
    }

    public function test_it_clears_columns_on_empty_order(): void
    {
        $this->sut->setColumns(['foo', 'bar']);
        $this->sut->setOrder('');
        $this->assertSame([], $this->sut->getColumns());
        $this->assertSame('', $this->sut->getOrder());
    }
}
