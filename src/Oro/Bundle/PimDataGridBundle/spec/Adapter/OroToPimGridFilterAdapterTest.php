<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;

class OroToPimGridFilterAdapterTest extends TestCase
{
    private MassActionDispatcher|MockObject $massActionDispatcher;
    private OroToPimGridFilterAdapter $sut;

    protected function setUp(): void
    {
        $this->massActionDispatcher = $this->createMock(MassActionDispatcher::class);
        $this->sut = new OroToPimGridFilterAdapter($this->massActionDispatcher);
    }

    public function test_it_returns_raw_filters(): void
    {
        $this->massActionDispatcher->method('getRawFilters')->with(['gridName' => 'product-grid'])->willReturn([
                    [
                        'field'    => 'sku',
                        'operator' => 'CONTAINS',
                        'value'    => 'DP',
                    ],
                    [
                        'field'    => 'categories',
                        'operator' => 'IN',
                        'value'    => [12, 13, 14],
                    ],
                ]);
        $this->assertSame([
                    [
                        'field'    => 'sku',
                        'operator' => 'CONTAINS',
                        'value'    => 'DP',
                    ],
                    [
                        'field'    => 'categories',
                        'operator' => 'IN',
                        'value'    => [12, 13, 14],
                    ],
                ], $this->sut->adapt(['gridName' => 'product-grid']));
    }

    public function test_it_returns_filters_on_family_grid(): void
    {
        $family1 = $this->createMock(FamilyInterface::class);
        $family2 = $this->createMock(FamilyInterface::class);

        $parameters = [
                    'gridName' => 'family-grid',
                    'inset'    => true,
                    'filters'  => ['myfilter' => 'value'],
                ];
        $this->massActionDispatcher->method('dispatch')->with([
                    'gridName' => 'family-grid',
                    'inset'    => true,
                    'filters'  => [],
                ])->willReturn([$family1, $family2]);
        $family1->method('getId')->willReturn(45);
        $family2->method('getId')->willReturn(70);
        $this->massActionDispatcher->expects($this->never())->method('getRawFilters')->with($this->anything());
        $this->assertSame([[
                    'field'    => 'id',
                    'operator' => 'IN',
                    'value'    => [45, 70],
                ]], $this->sut->adapt($parameters));
    }
}
