<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily\OperationValue;
use Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily\OperationValueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class OperationValueValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private OperationValueValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new OperationValueValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_should_validate_operation_value_int_in_string(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate('1', $constraint);
    }

    public function test_it_should_validate_operation_value_float_in_string(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->never())->method('addViolation')->with($this->anything());
        $this->sut->validate('0.00000006', $constraint);
    }

    public function test_it_add_violation_when_operation_value_is_null(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->once())->method('addViolation')->with('This value should not be blank.', ['{{ value }}' => 'null']);
        $this->sut->validate(null, $constraint);
    }

    public function test_it_add_violation_when_operation_value_is_empty(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->once())->method('addViolation')->with('This value should not be blank.', ['{{ value }}' => '""']);
        $this->sut->validate('', $constraint);
    }

    public function test_it_add_violation_when_operation_value_is_an_array(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->once())->method('addViolation')->with('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', []);
        $this->sut->validate(['value' => '10'], $constraint);
    }

    public function test_it_add_violation_when_operation_value_is_a_number(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->once())->method('addViolation')->with('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', []);
        $this->sut->validate(16.88888, $constraint);
    }

    public function test_it_add_violation_when_operation_value_is_a_scientific_notation(): void
    {
        $constraint = $this->createMock(OperationValue::class);

        $this->context->expects($this->once())->method('addViolation')->with('pim_measurements.validation.measurement_family.convert.value_should_be_a_number_in_a_string', []);
        $this->sut->validate('10E-7', $constraint);
    }
}
