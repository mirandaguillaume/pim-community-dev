<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\UnitNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamilyCode;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PHPUnit\Framework\TestCase;

class MeasurementFamilyTest extends TestCase
{
    private const MEASUREMENT_FAMILY_CODE = 'Length';
    private const MEASUREMENT_FAMILY_LABEL = ['en_US' => 'Length', 'fr_FR' => 'Longueur'];
    private const METER_UNIT_CODE = 'METER';
    private const METER_LABELS = ['en_US' => 'meter', 'fr_FR' => 'mètre'];
    private const METER_SYMBOL = 'm';
    private const CENTIMETER_UNIT_CODE = 'CENTIMETER';
    private const CENTIMETER_LABELS = ['en_US' => 'centimeter', 'fr_FR' => 'centimètre'];
    private const CENTIMETER_SYMBOL = 'cm';

    private MeasurementFamily $sut;

    protected function setUp(): void
    {
        $standardUnitCode = UnitCode::fromString(self::METER_UNIT_CODE);
        $meterUnit = Unit::create(
            $standardUnitCode,
            LabelCollection::fromArray(self::METER_LABELS),
            [Operation::create('mul', '1')],
            self::METER_SYMBOL,
        );
        $centimeterUnit = Unit::create(
            UnitCode::fromString(self::CENTIMETER_UNIT_CODE),
            LabelCollection::fromArray(self::CENTIMETER_LABELS),
            [Operation::create('mul', '5')],
            self::CENTIMETER_SYMBOL,
        );
        $this->sut = MeasurementFamily::create(
            MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
            LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
            $standardUnitCode,
            [$meterUnit, $centimeterUnit],
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(MeasurementFamily::class, $this->sut);
    }

    public function test_it_should_be_able_to_normalize_itself(): void
    {
        $this->assertSame([
                        'code'               => self::MEASUREMENT_FAMILY_CODE,
                        'labels'             => self::MEASUREMENT_FAMILY_LABEL,
                        'standard_unit_code' => self::METER_UNIT_CODE,
                        'units'              => [
                            [
                                'code'                  => self::METER_UNIT_CODE,
                                'labels'                => self::METER_LABELS,
                                'convert_from_standard' => [['operator' => 'mul', 'value' => '1']],
                                'symbol'                => self::METER_SYMBOL,
                            ],
                            [
                                'code'                  => self::CENTIMETER_UNIT_CODE,
                                'labels'                => self::CENTIMETER_LABELS,
                                'convert_from_standard' => [['operator' => 'mul', 'value' => '5']],
                                'symbol'                => self::CENTIMETER_SYMBOL,
                            ],
                        ],
                    ], $this->sut->normalize());
    }

    public function test_it_should_not_be_able_create_a_measurement_family_having_no_units(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(
            MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
            LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
            UnitCode::fromString(self::METER_UNIT_CODE),
            [],
        );
    }

    public function test_it_should_not_be_able_to_create_a_measurement_family_having_a_standard_unit_not_being_in_the_units(): void
    {
        $unknownUnitCode = 'unknown_unit_code';
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(
            MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
            LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
            UnitCode::fromString($unknownUnitCode),
            [
                                Unit::create(
                                    UnitCode::fromString(self::METER_UNIT_CODE),
                                    LabelCollection::fromArray(self::METER_LABELS),
                                    [Operation::create('mul', '1')],
                                    self::METER_SYMBOL
                                ),
                                Unit::create(
                                    UnitCode::fromString(self::CENTIMETER_SYMBOL),
                                    LabelCollection::fromArray(self::CENTIMETER_LABELS),
                                    [Operation::create('mul', '1')],
                                    self::CENTIMETER_SYMBOL
                                ),
                            ],
        );
    }

    public function test_it_should_not_be_able_to_create_if_there_are_unit_duplicates(): void
    {
        $meterUnit = Unit::create(
            UnitCode::fromString(self::METER_UNIT_CODE),
            LabelCollection::fromArray(self::METER_LABELS),
            [Operation::create('mul', '1')],
            self::METER_SYMBOL
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(
            MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
            LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
            $meterUnit->code(),
            [$meterUnit, $meterUnit],
        );
    }

    public function test_it_returns_the_label_of_the_provided_unit_for_the_provided_locale(): void
    {
        $this->assertSame('centimètre', $this->sut->getUnitLabel(
            UnitCode::fromString(self::CENTIMETER_UNIT_CODE),
            LocaleIdentifier::fromCode('fr_FR')
        ));
    }

    public function test_it_should_throw_when_the_provided_unit_is_not_found(): void
    {
        $this->expectException(UnitNotFoundException::class);
        $this->sut->getUnitLabel(
            UnitCode::fromString('UNKNOWN'),
            LocaleIdentifier::fromCode('fr_FR'),
        );
    }

    public function test_it_should_not_be_able_to_create_if_the_standard_unit_conversion_is_not_a_multiply_by_one(): void
    {
        $invalidStandardUnitOperation = Operation::create('mul', '5');
        $this->expectException(\Exception::class);
        $this->sut->create(
            MeasurementFamilyCode::fromString(self::MEASUREMENT_FAMILY_CODE),
            LabelCollection::fromArray(self::MEASUREMENT_FAMILY_LABEL),
            UnitCode::fromString('invalid_standard_unit_code'),
            [
                                Unit::create(
                                    UnitCode::fromString('invalid_standard_unit_code'),
                                    LabelCollection::fromArray([]),
                                    [$invalidStandardUnitOperation],
                                    ''
                                ),
                            ],
        );
    }
}
