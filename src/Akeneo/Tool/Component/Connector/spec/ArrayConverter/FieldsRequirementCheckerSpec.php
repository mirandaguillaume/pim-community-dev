<?php

namespace spec\Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PhpSpec\ObjectBehavior;

class FieldsRequirementCheckerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(FieldsRequirementChecker::class);
    }

    public function it_does_not_raise_exception_when_there_is_no_required_fields()
    {
        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->during('checkFieldsPresence', [['foo' => 'bar'], []]);
    }

    public function it_does_not_raise_exception_when_all_required_fields_are_filled()
    {
        $this
            ->shouldNotThrow(StructureArrayConversionException::class)
            ->during('checkFieldsPresence', [['foo' => 'bar'], ['foo']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_blank()
    {
        $this
            ->shouldThrow(DataArrayConversionException::class)
            ->during('checkFieldsFilling', [['foo' => ''], ['foo']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_null()
    {
        $this
            ->shouldThrow(DataArrayConversionException::class)
            ->during('checkFieldsFilling', [['foo' => null], ['foo']]);
    }

    public function it_should_raise_exception_when_a_required_field_is_not_present()
    {
        $this
            ->shouldThrow(StructureArrayConversionException::class)
            ->during('checkFieldsPresence', [['foo' => 'bar'], ['baz']]);
    }
}
