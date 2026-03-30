<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\Localization\Validator\Constraints;

use Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormat;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use spec\Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormatValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class NumberFormatValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private NumberFormatValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new NumberFormatValidator(['.' => 'dot (.)']);
        $this->sut->initialize($this->context);
    }

    public function test_it_does_not_add_violation_null_value(): void
    {
        $constraint = $this->createMock(NumberFormat::class);

        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(null, $constraint);
    }

    public function test_it_does_not_add_violation_when_format_is_respected(): void
    {
        $constraint = $this->createMock(NumberFormat::class);

        $this->context->expects($this->never())->method('buildViolation');
        $constraint->decimalSeparator = ',';
        $constraint->path = 'constraint_path';
        $this->sut->validate('12,45', $constraint);
        $constraint->decimalSeparator = '.';
        $this->sut->validate('12.45', $constraint);
        $this->sut->validate('12', $constraint);
        $this->sut->validate('0', $constraint);
        $this->sut->validate(0, $constraint);
    }

    public function test_it_adds_violation_when_format_is_not_respected(): void
    {
        $constraint = $this->createMock(NumberFormat::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $decimalSeparator = '.';
        $constraint->decimalSeparator = $decimalSeparator;
        $constraint->path = 'constraint_path';
        $this->context->expects($this->once())->method('buildViolation')->with(
            'This type of value expects the use of dot (.) to separate decimals.',
            ['{{ decimal_separator }}' => '.']
        )->willReturn($violation);
        $violation->method('atPath')->with($this->anything())->willReturn($violation);
        $violation->method('addViolation')->willReturn(null);
        $this->sut->validate('12,45', $constraint);
    }
}
