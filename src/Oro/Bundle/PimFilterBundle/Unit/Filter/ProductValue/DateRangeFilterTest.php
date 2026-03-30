<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Oro\Bundle\PimFilterBundle\Filter\ProductValue\AbstractDateFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Oro\Bundle\PimFilterBundle\Filter\ProductValue\DateRangeFilter;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class DateRangeFilterTest extends TestCase
{
    private FormFactoryInterface|MockObject $factory;
    private ProductFilterUtility|MockObject $utility;
    private DateRangeFilter $sut;

    protected function setUp(): void
    {
        $this->factory = $this->createMock(FormFactoryInterface::class);
        $this->utility = $this->createMock(ProductFilterUtility::class);
        $this->sut = new DateRangeFilter($this->factory, $this->utility);
        $this->sut->init(
            'date_filter',
            [
        ProductFilterUtility::DATA_NAME_KEY => 'data_name_key',
        ]
        );
    }

    public function test_it_is_a_flexible_date_filter(): void
    {
        $this->assertInstanceOf(AbstractDateFilter::class, $this->sut);
    }

    public function test_it_has_a_name(): void
    {
        $this->assertSame('date_filter', $this->sut->getName());
    }

    public function test_it_parses_two_datetime_objects(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => '2014-01-23',
                    'type'       => 1,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => 1,
                ]));
    }

    public function test_it_parses_one_start_date(): void
    {
        $start = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => null,
                    'type'       => 1,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                    ],
                    'type'  => 1,
                ]));
    }

    public function test_it_parses_one_end_date(): void
    {
        $end = $this->createMock(DateTime::class);

        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => null,
                    'date_end'   => '2014-01-23',
                    'type'       => 1,
                ], $this->sut->parseData([
                    'value' => [
                        'end' => $end,
                    ],
                    'type'  => 1,
                ]));
    }

    public function test_it_parses_between_type_range(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => '2014-01-23',
                    'type'       => 1,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => DateRangeFilterType::TYPE_BETWEEN,
                ]));
    }

    public function test_it_parses_not_between_type_range(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => '2014-01-23',
                    'type'       => 2,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => DateRangeFilterType::TYPE_NOT_BETWEEN,
                ]));
    }

    public function test_it_parses_more_than_type_range(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => null,
                    'type'       => 3,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => DateRangeFilterType::TYPE_MORE_THAN,
                ]));
    }

    public function test_it_parses_less_than_type_range(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => null,
                    'date_end'   => '2014-01-23',
                    'type'       => 4,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => DateRangeFilterType::TYPE_LESS_THAN,
                ]));
    }

    public function test_it_fallbacks_on_between_type_range(): void
    {
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->assertSame([
                    'date_start' => '1987-05-14',
                    'date_end'   => '2014-01-23',
                    'type'       => 1,
                ], $this->sut->parseData([
                    'value' => [
                        'start' => $start,
                        'end'   => $end,
                    ],
                    'type'  => 'unknown',
                ]));
    }

    public function test_it_does_not_parse_something_else_than_an_array(): void
    {
        $this->assertSame(false, $this->sut->parseData('foo'));
        $this->assertSame(false, $this->sut->parseData(0));
        $this->assertSame(false, $this->sut->parseData(true));
        $this->assertSame(false, $this->sut->parseData(new \StdClass()));
    }

    public function test_it_does_not_parse_array_without_value_key(): void
    {
        $this->assertSame(false, $this->sut->parseData([]));
    }

    public function test_it_does_not_parse_array_without_type_key(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => ['start' => '1987-05-14']]));
    }

    public function test_it_does_not_parse_array_without_value_key_of_type_array(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => true, 'type' => 1]));
    }

    public function test_it_does_not_parse_array_without_start_and_end_values(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => [], 'type' => 1]));
    }

    public function test_it_does_not_parse_array_with_not_datetime_type_start(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => ['start' => 'yesterday'], 'type' => 1]));
    }

    public function test_it_does_not_parse_array_with_not_datetime_type_end(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => ['end' => 'tomorrow'], 'type' => 1]));
    }

    public function test_it_does_not_parse_array_with_more_than_filter_and_no_start_date(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => ['start' => null], 'type' => DateRangeFilterType::TYPE_MORE_THAN]));
    }

    public function test_it_does_not_parse_array_with_less_than_filter_and_no_end_date(): void
    {
        $this->assertSame(false, $this->sut->parseData(['value' => ['end' => null], 'type' => DateRangeFilterType::TYPE_LESS_THAN]));
    }

    public function test_it_applies_between_date_range_filter(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', 'BETWEEN', ['1987-05-14', '2014-01-23']);
        $this->sut->apply(
            $datasource,
            [
                        'value' => [
                            'start' => $start,
                            'end'   => $end,
                        ],
                        'type'  => DateRangeFilterType::TYPE_BETWEEN,
                    ]
        );
    }

    public function test_it_applies_not_between_date_range_filter(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->utility->expects($this->once())->method('applyFilter')->with(
            $datasource,
            'data_name_key',
            'NOT BETWEEN',
            ['1987-05-14', '2014-01-23']
        );
        $this->sut->apply(
            $datasource,
            [
                        'value' => [
                            'start' => $start,
                            'end'   => $end,
                        ],
                        'type'  => DateRangeFilterType::TYPE_NOT_BETWEEN,
                    ]
        );
    }

    public function test_it_applies_less_than_date_range_filter(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', '<', '2014-01-23');
        $this->sut->apply(
            $datasource,
            [
                        'value' => [
                            'start' => $start,
                            'end'   => $end,
                        ],
                        'type'  => DateRangeFilterType::TYPE_LESS_THAN,
                    ]
        );
    }

    public function test_it_applies_more_than_date_range_filter(): void
    {
        $datasource = $this->createMock(FilterDatasourceAdapterInterface::class);
        $start = $this->createMock(DateTime::class);
        $end = $this->createMock(DateTime::class);

        $start->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $end->expects($this->once())->method('setTimezone')->with(/* TODO: convert Argument matcher */ Argument::allOf($this->isType('\DateTimeZone'), Argument::which('getName', 'UTC')));
        $start->method('format')->with('Y-m-d')->willReturn('1987-05-14');
        $end->method('format')->with('Y-m-d')->willReturn('2014-01-23');
        $this->utility->expects($this->once())->method('applyFilter')->with($datasource, 'data_name_key', '>', '1987-05-14');
        $this->sut->apply(
            $datasource,
            [
                        'value' => [
                            'start' => $start,
                            'end'   => $end,
                        ],
                        'type'  => DateRangeFilterType::TYPE_MORE_THAN,
                    ]
        );
    }

    public function test_it_provides_date_range_form(): void
    {
        $form = $this->createMock(Form::class);

        $this->factory->method('create')->with(DateRangeFilterType::class, [], ['csrf_protection' => false])->willReturn($form);
        $this->assertSame($form, $this->sut->getForm());
    }
}
