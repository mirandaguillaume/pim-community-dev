<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\LengthValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LengthValidatorTest extends TestCase
{
    private LengthValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new LengthValidator();
    }

    public function test_it_allows_null_value(): void
    {
        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
                    ->buildViolation($this->anything())
                    ->shouldNotBeCalled();
        $this->sut->validate(null, $constraint);
    }

    public function test_it_allows_empty_value(): void
    {
        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
                    ->buildViolation($this->anything())
                    ->shouldNotBeCalled();
        $this->sut->validate('', $constraint);
    }

    public function test_it_does_not_validate_a_too_long_string(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new Length(['max' => 5, 'attributeCode' => 'a_code']);
        $context
                    ->buildViolation($constraint->maxMessage)
                    ->shouldBeCalled()
                    ->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('setParameter')->with('%attribute%', $constraint->attributeCode)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('setParameter')->with('%limit%', $constraint->max)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('setInvalidValue')->with('azertyu')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('setPlural')->with((int) $constraint->max)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('setCode')->with(Length::TOO_LONG_ERROR)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation')->willReturn($constraintViolationBuilder);
        $this->sut->validate('azertyu', $constraint);
    }

    public function test_it_throws_an_exception_if_the_constraint_is_not_a_length(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('value', new IsString());
    }
}
