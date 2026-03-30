<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Extension\Pager;

use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PagerResolverTest extends TestCase
{
    private PagerInterface|MockObject $orm;
    private PagerInterface|MockObject $dummy;
    private PagerResolver $sut;

    protected function setUp(): void
    {
        $this->orm = $this->createMock(PagerInterface::class);
        $this->dummy = $this->createMock(PagerInterface::class);
        $this->sut = new PagerResolver($this->orm, $this->dummy, ['foo', 'bar']);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(PagerResolver::class, $this->sut);
    }

    public function test_it_returns_an_orm_pager_for_non_product_grids(): void
    {
        $this->assertSame($this->orm, $this->sut->getPager('baz'));
    }

    public function test_it_returns_a_dummy_pager_for_product_grids(): void
    {
        $this->assertSame($this->dummy, $this->sut->getPager('foo'));
        $this->assertSame($this->dummy, $this->sut->getPager('bar'));
    }
}
