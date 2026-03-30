<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumeric;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\IsNumericValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class IsNumericValidatorTest extends TestCase
{
    private ExecutionContextInterface|MockObject $context;
    private ConstraintViolationListInterface|MockObject $constraintViolationList;
    private IsNumericValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->sut = new IsNumericValidator();
        $this->sut->initialize($this->context);
        $this->context->method('getViolations')->willReturn($this->constraintViolationList);
        $this->constraintViolationList->method('count')->willReturn(0);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IsNumericValidator::class, $this->sut);
    }

    public function test_it_is_a_validator_constraint(): void
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidator', $this->sut);
    }

    public function test_it_does_not_add_violation_null_value(): void
    {
        $numericConstraint = $this->createMock(IsNumeric::class);

        $this->context->expects($this->never())->method('buildViolation');
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(null, $numericConstraint);
    }

    public function test_it_does_not_add_violation_metric_with_no_data(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $numericConstraint = $this->createMock(IsNumeric::class);

        $metric->method('getData')->willReturn(null);
        $this->context->expects($this->never())->method('buildViolation');
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($metric, $numericConstraint);
    }

    public function test_it_does_not_add_violation_product_price_with_no_data(): void
    {
        $productPrice = $this->createMock(ProductPriceInterface::class);
        $numericConstraint = $this->createMock(IsNumeric::class);

        $productPrice->method('getData')->willReturn(null);
        $this->context->expects($this->never())->method('buildViolation');
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($productPrice, $numericConstraint);
    }

    public function test_it_does_not_add_violation_when_validates_numeric_value(): void
    {
        $numericConstraint = $this->createMock(IsNumeric::class);

        $propertyPath = null;
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(5, $numericConstraint);
    }

    public function test_it_does_not_add_violation_when_validates_numeric_metric_value(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $numericConstraint = $this->createMock(IsNumeric::class);

        $metric->method('getData')->willReturn(5);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($metric, $numericConstraint);
    }

    public function test_it_does_not_add_violation_when_validates_numeric_product_price_value(): void
    {
        $productPrice = $this->createMock(ProductPriceInterface::class);
        $numericConstraint = $this->createMock(IsNumeric::class);

        $productPrice->method('getData')->willReturn(5);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate($productPrice, $numericConstraint);
    }

    public function test_it_adds_violation_when_validating_non_numeric_value(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $this->context->method('buildViolation')->with(IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                        [
                            '{{ attribute }}' => $numericConstraint->attributeCode,
                            '{{ value }}' => 'a',
                        ])->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate('a', $numericConstraint);
    }

    public function test_it_adds_violation_when_validating_non_numeric_metric_value(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $metric->method('getData')->willReturn('a');
        $this->context->expects($this->once())->method('buildViolation')->with(IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                        [
                            '{{ attribute }}' => $numericConstraint->attributeCode,
                            '{{ value }}' => 'a',
                        ])->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->method('atPath')->with('data')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($metric, $numericConstraint);
    }

    public function test_it_adds_violation_when_validating_non_numeric_product_price_value(): void
    {
        $productPrice = $this->createMock(ProductPriceInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $productPrice->method('getData')->willReturn('a');
        $this->context->expects($this->once())->method('buildViolation')->with(IsNumeric::SHOULD_BE_NUMERIC_MESSAGE,
                        [
                            '{{ attribute }}' => $numericConstraint->attributeCode,
                            '{{ value }}' => 'a',
                        ])->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->method('atPath')->with('data')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($productPrice, $numericConstraint);
    }

    public function test_it_adds_violation_when_validating_numeric_value_with_space(): void
    {
        $metric = $this->createMock(MetricInterface::class);
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $numericConstraint = new IsNumeric();
        $numericConstraint->attributeCode = 'number';
        $metric->method('getData')->willReturn(' 3.14');
        $this->context->expects($this->once())->method('buildViolation')->with(IsNumeric::SHOULD_NOT_CONTAINS_SPACE_MESSAGE,
                        [
                            '{{ attribute }}' => $numericConstraint->attributeCode,
                            '{{ value }}' => ' 3.14',
                        ])->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with(IsNumeric::IS_NUMERIC)->willReturn($violationBuilder);
        $violationBuilder->method('atPath')->with('data')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($metric, $numericConstraint);
    }
}
