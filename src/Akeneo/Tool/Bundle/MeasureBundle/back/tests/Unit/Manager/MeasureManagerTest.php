<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Manager;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Symfony\Component\Yaml\Yaml;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureManagerTest extends TestCase
{
    private LegacyMeasurementProvider|MockObject $provider;
    private MeasureManager $sut;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(LegacyMeasurementProvider::class);
        $this->sut = new MeasureManager($this->provider);
        $yaml = <<<YAML
        measures_config:
        Length:
        standard: METER
        units:
        CENTIMETER:
        convert: [{'div': 0.01}]
        format: cm
        METER:
        convert: [{'test': 1}]
        format: m
        Weight:
        standard: GRAM
        units:
        MILLIGRAM:
        convert: [{'mul': 0.001}]
        symbol: mg
        GRAM:
        convert: [{'mul': 1}]
        symbol: g
        KILOGRAM:
        convert: [{'mul': 1000}]
        symbol: kg
        YAML;
        $config = Yaml::parse($yaml);
        $this->provider->method('getMeasurementFamilies')->willReturn($config['measures_config']);
    }

    public function test_it_throws_an_exception_when_try_to_get_symbols_of_unknown_family(): void
    {
        $this->expectException(new MeasurementFamilyNotFoundException('Undefined measure family "foo"'));
        $this->sut->getUnitSymbolsForFamily('foo');
        $this->expectException(new MeasurementFamilyNotFoundException('Undefined measure family "foo"'));
        $this->sut->getUnitCodesForFamily('foo');
    }

    public function test_it_returns_unit_symbols_list_from_a_family(): void
    {
        $this->assertSame([
                            'MILLIGRAM' => 'mg',
                            'GRAM'      => 'g',
                            'KILOGRAM'  => 'kg',
                        ], $this->sut->getUnitSymbolsForFamily('Weight'));
    }

    public function test_it_indicates_whether_or_not_a_unit_symbol_exists_for_a_family(): void
    {
        $this->assertSame(true, $this->sut->unitSymbolExistsInFamily('mg', 'Weight'));
        $this->assertSame(false, $this->sut->unitSymbolExistsInFamily('foo', 'Weight'));
    }

    public function test_it_indicates_whether_or_not_a_family_exists(): void
    {
        $this->assertSame(true, $this->sut->familyExists('Weight'));
        $this->assertSame(false, $this->sut->familyExists('unknown_family'));
    }

    public function test_it_returns_standard_unit_for_a_family(): void
    {
        $this->assertSame('GRAM', $this->sut->getStandardUnitForFamily('Weight'));
    }

    public function test_it_returns_unit_codes_for_a_family(): void
    {
        $this->assertSame(['MILLIGRAM', 'GRAM', 'KILOGRAM'], $this->sut->getUnitCodesForFamily('Weight'));
    }

    public function test_it_indicates_whether_or_not_a_unit_code_exists_for_a_family(): void
    {
        $this->assertSame(true, $this->sut->unitCodeExistsInFamily('GRAM', 'Weight'));
        $this->assertSame(false, $this->sut->unitCodeExistsInFamily('FOO', 'Weight'));
    }
}
