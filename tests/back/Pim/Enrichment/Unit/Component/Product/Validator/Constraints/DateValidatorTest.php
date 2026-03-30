<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Date;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\DateValidator;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsString;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\DateValidator as BaseDateValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DateValidatorTest extends TestCase
{
    private DateValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new DateValidator();
    }

    public function test_it_allows_null_value(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $constraint = new Date(['attributeCode' => 'a_code']);
        $context->method('getViolations')->willReturn(new ConstraintViolationList());
        $context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(null, $constraint);
    }

    public function test_it_throws_an_exception_if_the_constraint_is_not_a_date(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('value', new IsString());
    }

    public function test_it_validates_a_good_url(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);

        $goodDate = '2021-02-01';
        $constraint = new Date(['attributeCode' => 'a_code']);
        $context->method('getViolations')->willReturn(new ConstraintViolationList());
        $context->expects($this->never())->method('buildViolation');
        $this->sut->validate($goodDate, $constraint);
    }

    public function test_it_does_not_validate_a_bad_date(): void
    {
        $constraintViolation = $this->createMock(ConstraintViolation::class);
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $badDate = '2021/02-01';
        $constraint = new Date(['attributeCode' => 'a_code']);
        $context->buildViolation($this->anything())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setParameter')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setCode')->with($this->anything())->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $constraintViolationList = new ConstraintViolationList([$constraintViolation]);
        $context->getViolations()->willReturn($constraintViolationList);
        $constraintViolation->method('getCode')->willReturn(Date::INVALID_FORMAT_ERROR);
        $context->buildViolation($constraint->message)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->exactly(1))->method('setParameter')->with('%attribute%', $constraint->attributeCode)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->exactly(1))->method('setInvalidValue')->with($badDate)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->exactly(2))->method('setCode')->with(Date::INVALID_FORMAT_ERROR)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->exactly(2))->method('addViolation');
        $this->sut->validate($badDate, $constraint);
    }
}
