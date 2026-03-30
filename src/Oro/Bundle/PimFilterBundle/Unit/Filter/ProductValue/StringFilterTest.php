<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue\StringFilter;
use Symfony\Component\Form\FormFactoryInterface;

class StringFilterTest extends TestCase
{
    private StringFilter $sut;

    protected function setUp(): void
    {
    }

    public function test_it_applies_on_starts_with_with_zero_value(): void
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $utility = $this->createMock(ProductFilterUtility::class);
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->sut = new StringFilter($factory, $utility);
        $this->sut->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
        $utility->expects($this->once())->method('applyFilter')->with($ds, 'bar', 'STARTS WITH', '0');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 4, 'value' => '0']));
    }

    public function test_it_applies_on_empty_type(): void
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $utility = $this->createMock(ProductFilterUtility::class);
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->sut = new StringFilter($factory, $utility);
        $this->sut->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
        $utility->expects($this->once())->method('applyFilter')->with($ds, 'bar', 'EMPTY', '');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 'empty', 'value' => '']));
    }

    public function test_it_does_not_apply_on_empty_value(): void
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $utility = $this->createMock(ProductFilterUtility::class);
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->sut = new StringFilter($factory, $utility);
        $this->sut->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
        $utility->expects($this->never())->method('applyFilter');
        $this->assertSame(false, $this->sut->apply($ds, ['type' => 3, 'value' => '']));
    }

    public function test_it_applies_on_starts_with_with_value_containing_underscore(): void
    {
        $factory = $this->createMock(FormFactoryInterface::class);
        $utility = $this->createMock(ProductFilterUtility::class);
        $ds = $this->createMock(FilterDatasourceAdapterInterface::class);

        $this->sut = new StringFilter($factory, $utility);
        $this->sut->init('foo', [ProductFilterUtility::DATA_NAME_KEY => 'bar']);
        $utility->expects($this->once())->method('applyFilter')->with($ds, 'bar', 'STARTS WITH', 'my_value');
        $this->assertSame(true, $this->sut->apply($ds, ['type' => 4, 'value' => 'my_value']));
    }
}
