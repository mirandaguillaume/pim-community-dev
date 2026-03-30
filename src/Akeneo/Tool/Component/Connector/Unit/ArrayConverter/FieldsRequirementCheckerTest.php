<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PHPUnit\Framework\TestCase;

class FieldsRequirementCheckerTest extends TestCase
{
    private FieldsRequirementChecker $sut;

    protected function setUp(): void
    {
        $this->sut = new FieldsRequirementChecker();
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FieldsRequirementChecker::class, $this->sut);
    }

    public function test_it_does_not_raise_exception_when_there_is_no_required_fields(): void
    {
        $this->sut->shouldNotThrow(StructureArrayConversionException::class)
                    ->during('checkFieldsPresence', [['foo' => 'bar'], []]);
    }

    public function test_it_does_not_raise_exception_when_all_required_fields_are_filled(): void
    {
        $this->sut->shouldNotThrow(StructureArrayConversionException::class)
                    ->during('checkFieldsPresence', [['foo' => 'bar'], ['foo']]);
    }

    public function test_it_should_raise_exception_when_a_required_field_is_blank(): void
    {
        $this->expectException(DataArrayConversionException::class);
        $this->sut->checkFieldsFilling(['foo' => ''], ['foo']);
    }

    public function test_it_should_raise_exception_when_a_required_field_is_null(): void
    {
        $this->expectException(DataArrayConversionException::class);
        $this->sut->checkFieldsFilling(['foo' => null], ['foo']);
    }

    public function test_it_should_raise_exception_when_a_required_field_is_not_present(): void
    {
        $this->expectException(StructureArrayConversionException::class);
        $this->sut->checkFieldsPresence(['foo' => 'bar'], ['baz']);
    }
}
