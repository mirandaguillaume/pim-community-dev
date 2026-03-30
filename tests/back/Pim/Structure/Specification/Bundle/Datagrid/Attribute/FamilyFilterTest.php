<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Bundle\Datagrid\Attribute;

use Akeneo\Pim\Structure\Bundle\Datagrid\Attribute\FamilyFilter;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\PimFilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactoryInterface;

class FamilyFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private FilterUtility|MockObject $util;
    private FamilyFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->util = $this->createMock(FilterUtility::class);
        $this->sut = new FamilyFilter($this->factory, $this->util);
    }

    public function test_it_is_a_family_filter(): void
    {
        $this->assertInstanceOf(FamilyFilter::class, $this->sut);
    }

    public function test_it_does_nothing_if_filter_value_is_empty(): void
    {
        $ds = $this->createMock(OrmFilterDatasourceAdapter::class);

        $this->assertSame(false, $this->sut->apply($ds, null));
        $this->assertSame(false, $this->sut->apply($ds, []));
        $this->assertSame(false, $this->sut->apply($ds, ['value' => null]));
        $this->assertSame(false, $this->sut->apply($ds, ['value' => []]));
    }

    public function test_it_does_not_fail_if_the_filter_is_applied(): void
    {
        $ds = $this->createMock(OrmFilterDatasourceAdapter::class);
        $qb = $this->createMock(QueryBuilder::class);

        $ds->method('getQueryBuilder')->willReturn($qb);
        $qb->method('getRootAliases')->willReturn(['attribute']);
        $qb->expects($this->once())->method('innerJoin')->willReturn($qb);
        $qb->expects($this->once())->method('setParameter')->with(':families', [10, 20])->willReturn($qb);
        $this->assertSame(true, $this->sut->apply($ds, ['value' => [10, 20]]));
    }
}
