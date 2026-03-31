<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Convert;

use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MeasureConverterTest extends TestCase
{
    private LegacyMeasurementProvider|MockObject $provider;
    private MeasureConverter $sut;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(LegacyMeasurementProvider::class);
        $this->sut = new MeasureConverter($this->provider);
        $config = [
            'Length' => [
                'standard' => 'METER',
                'units' => [
                    'CENTIMETER' => [
                        'convert' => [['div' => 0.01]],
                        'format' => 'cm',
                    ],
                    'METER' => [
                        'convert' => [['test' => 1]],
                        'format' => 'm',
                    ],
                ],
            ],
            'Weight' => [
                'standard' => 'GRAM',
                'units' => [
                    'MILLIGRAM' => [
                        'convert' => [['mul' => 0.001]],
                        'symbol' => 'mg',
                    ],
                    'GRAM' => [
                        'convert' => [['mul' => 1]],
                        'symbol' => 'g',
                    ],
                    'KILOGRAM' => [
                        'convert' => [['mul' => 1000]],
                        'symbol' => 'kg',
                    ],
                ],
            ],
        ];
        $this->provider->method('getMeasurementFamilies')->willReturn($config);
    }

    public function test_it_allows_to_define_the_family(): void
    {
        $this->assertInstanceOf(MeasureConverter::class, $this->sut->setFamily('Length'));
        $this->assertInstanceOf(MeasureConverter::class, $this->sut->setFamily('length'));
    }

    public function test_it_throws_an_exception_if_an_unknown_family_is_set(): void
    {
        $this->expectException(MeasurementFamilyNotFoundException::class);
        $this->sut->setFamily('foo');
    }

    public function test_it_converts_a_value_from_a_base_unit_to_a_final_unit(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('1000000.000000000000', $this->sut->convert(
            'KILOGRAM',
            'MILLIGRAM',
            1
        ));
    }

    public function test_it_converts_a_value_from_a_base_unit_to_a_final_unit_case_insensitive(): void
    {
        $this->sut->setFamily('weight');
        $this->assertSame('1000000.000000000000', $this->sut->convert(
            'kilogram',
            'milligram',
            1
        ));
    }

    public function test_it_converts_a_value_to_a_standard_unit(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('1.000000000000', $this->sut->convertBaseToStandard('MILLIGRAM', 1000));
        $this->assertSame('1.000000000000', $this->sut->convertBaseToStandard('milligram', 1000));
    }

    public function test_it_converts_a_very_small_value_to_a_standard_unit(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('0.000100000000', $this->sut->convertBaseToStandard(
            'KILOGRAM',
            1.0E-7
        ));
    }

    public function test_it_returns_zero_when_value_is_not_numeric(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('0', $this->sut->convertBaseToStandard(
            'KILOGRAM',
            '1.900.000'
        ));
    }

    public function test_it_returns_zero_when_value_is_numeric_and_contains_space(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('0', $this->sut->convertBaseToStandard(
            'KILOGRAM',
            ' 1.900'
        ));
    }

    public function test_it_converts_a_standard_value_to_a_final_unit(): void
    {
        $this->sut->setFamily('Weight');
        $this->assertSame('0.010000000000', $this->sut->convertStandardToResult('KILOGRAM', 10));
        $this->assertSame('0.010000000000', $this->sut->convertStandardToResult('KiloGram', 10));
    }

    public function test_it_throws_an_exception_if_the_unit_measure_does_not_exist(): void
    {
        $this->sut->setFamily('Weight');
        $this->expectException(UnitNotFoundException::class);
        $this->expectExceptionMessage('Could not find metric unit "foo" in family "Weight"');
        $this->sut->convertBaseToStandard('foo', 1);
    }
}
