<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Validator\Constraints;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Akeneo\Tool\Component\Localization\Validator\Constraints\DateFormat;
use Akeneo\Tool\Component\Localization\Validator\Constraints\DateFormatValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DateFormatValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private DateFormatValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new DateFormatValidator(new DateFactory([]));
        $this->sut->initialize($this->context);
    }

    public function test_it_does_not_add_violation_null_value(): void
    {
        $constraint = $this->createMock(DateFormat::class);

        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->context->expects($this->never())->method('buildViolation');
        $this->assertNull($this->sut->validate(null, $constraint));
    }

    public function test_it_does_not_add_violation_when_format_is_respected(): void
    {
        $constraint = $this->createMock(DateFormat::class);

        $this->context->expects($this->never())->method('buildViolation');
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->assertNull($this->sut->validate('21/12/2015', $constraint));
        $format = 'yyyy-MM-dd';
        $constraint->dateFormat = $format;
        $this->assertNull($this->sut->validate('2015-12-21', $constraint));
        $date = 'Tuesday 31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $constraint->dateFormat = $format;
        $this->assertNull($this->sut->validate($date, $constraint));
    }

    public function test_it_adds_violation_when_validating_format_is_not_respected(): void
    {
        $constraint = $this->createMock(DateFormat::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $date = '2015-12-21';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ date_format }}' => $format])->willReturn($violation);
        $violation->method('atPath')->with($this->anything())->willReturn($violation);
        $violation->method('addViolation')->willReturn(null);
        $this->sut->validate($date, $constraint);
    }

    public function test_it_adds_violation_when_separators_are_not_respected(): void
    {
        $constraint = $this->createMock(DateFormat::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $date = '21-12-2015';
        $format = 'dd/MM/yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ date_format }}' => $format])->willReturn($violation);
        $violation->method('atPath')->with($this->anything())->willReturn($violation);
        $violation->method('addViolation')->willReturn(null);
        $this->sut->validate($date, $constraint);
    }

    public function test_it_adds_violation_when_separators_with_letter_are_not_respected(): void
    {
        $constraint = $this->createMock(DateFormat::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $date = 'Tuesday,31 December 2015';
        $format = 'EEEE dd MMMM yyyy';
        $constraint->dateFormat = $format;
        $constraint->path = 'constraint_path';
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ date_format }}' => $format])->willReturn($violation);
        $violation->method('atPath')->with($this->anything())->willReturn($violation);
        $violation->method('addViolation')->willReturn(null);
        $this->sut->validate($date, $constraint);
    }
}
