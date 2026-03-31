<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LabelCollection;
use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Operation;
use Akeneo\Tool\Bundle\MeasureBundle\Model\Unit;
use Akeneo\Tool\Bundle\MeasureBundle\Model\UnitCode;
use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    private const UNIT_CODE = 'METER';
    private const UNIT_LABELS = ['en_US' => 'Meter', 'fr_FR' => 'Metre'];
    private const OPERATION_OPERATOR = 'mul';
    private const OPERATION_VALUE = '1';
    private const SYMBOL = 'm';

    private Unit $sut;

    protected function setUp(): void
    {
        $this->sut = Unit::create(
            UnitCode::fromString(self::UNIT_CODE),
            LabelCollection::fromArray(self::UNIT_LABELS),
            [Operation::create(self::OPERATION_OPERATOR, self::OPERATION_VALUE)],
            self::SYMBOL,
        );
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(Unit::class, $this->sut);
    }

    public function test_it_can_be_normalized(): void
    {
        $this->assertSame([
                        'code'                  => self::UNIT_CODE,
                        'labels'                => self::UNIT_LABELS,
                        'convert_from_standard' => [
                            ['operator' => self::OPERATION_OPERATOR, 'value' => self::OPERATION_VALUE],
                        ],
                        'symbol'                => self::SYMBOL,
                    ], $this->sut->normalize());
    }

    public function test_it_cannot_created_with_something_else_than_a_list_of_operations(): void
    {
        $wrongOperation = 1234;
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(
            UnitCode::fromString(self::UNIT_CODE),
            LabelCollection::fromArray(self::UNIT_LABELS),
            [$wrongOperation],
            self::SYMBOL,
        );
    }

    public function test_it_should_be_created_with_at_least_one_operation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->create(
            UnitCode::fromString(self::UNIT_CODE),
            LabelCollection::fromArray(self::UNIT_LABELS),
            [],
            self::SYMBOL,
        );
    }

    public function test_it_returns_the_label_of_the_provided_locale(): void
    {
        $this->assertSame('metre', $this->sut->getLabel(LocaleIdentifier::fromCode('fr_FR')));
    }

    public function test_it_returns_the_code_between_brackets_when_there_is_no_label_for_the_locale(): void
    {
        $this->assertSame('[meter]', $this->sut->getLabel(LocaleIdentifier::fromCode('UNKNOWN')));
    }

    public function test_it_tells_if_it_is_a_standard_unit(): void
    {
        $this->sut = Unit::create(
            UnitCode::fromString(self::UNIT_CODE),
            LabelCollection::fromArray(self::UNIT_LABELS),
            [Operation::create('mul', '1')],
            self::SYMBOL,
        );
    }

    public function test_it_tells_if_it_is_not_be_a_standard_unit(): void
    {
        $this->assertSame(false, $this->sut->canBeAStandardUnit());
    }
}
