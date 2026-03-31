<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\Oro\Bundle\PimDataGridBundle\Adapter;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    public function test_it_returns_filters_on_attribute_grid_with_inset(): void
    {
        $parameters = [
            'gridName' => 'attribute-grid',
            'inset'    => true,
            'values'   => ['attr1', 'attr2'],
        ];
        $result = $this->sut->adapt($parameters);
        $this->assertSame([
            'search' => null,
            'options' => [
                'identifiers' => ['attr1', 'attr2'],
            ],
        ], $result);
    }

    public function test_it_returns_filters_on_attribute_grid_without_inset(): void
    {
        $parameters = [
            'gridName' => 'attribute-grid',
            'inset'    => false,
            'values'   => ['attr1'],
            'filters'  => [
                'label' => ['value' => 'my_search'],
                'code'  => 'code_val',
                'type'  => ['value' => ['text', '']],
                'group' => ['value' => ['general']],
                'scopable' => ['value' => '1'],
                'localizable' => ['value' => '2'],
                'family' => ['value' => ['shirts']],
                'smart' => ['value' => null],
                'quality' => ['value' => 'A'],
            ],
        ];
        $result = $this->sut->adapt($parameters);
        $this->assertSame('my_search', $result['search']);
        $this->assertSame(['attr1'], $result['options']['excluded_identifiers']);
        $this->assertSame(['text'], $result['options']['types']);
        $this->assertSame(['general'], $result['options']['attribute_groups']);
        $this->assertTrue($result['options']['scopable']);
        $this->assertFalse($result['options']['localizable']);
        $this->assertSame(['shirts'], $result['options']['families']);
        $this->assertNull($result['options']['smart']);
        $this->assertSame('A', $result['options']['quality']);
    }

    public function test_it_adapts_default_grid_with_inset_false(): void
    {
        $family1 = $this->createMock(FamilyInterface::class);

        $parameters = [
            'gridName' => 'family-grid',
            'inset'    => false,
            'filters'  => ['myfilter' => 'value'],
        ];

        // inset is false, so filters should NOT be cleared
        $this->massActionDispatcher->method('dispatch')->with($parameters)->willReturn([$family1]);
        $family1->method('getId')->willReturn(45);

        $result = $this->sut->adapt($parameters);
        $this->assertSame([[
            'field'    => 'id',
            'operator' => 'IN',
            'value'    => [45],
        ]], $result);
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
