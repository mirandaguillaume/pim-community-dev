<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleSelectPropertyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleSelectPropertyShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleSelectPropertyShouldBeValidValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $globalValidator;
    private ExecutionContext|MockObject $context;
    private ContextualValidatorInterface|MockObject $contextualValidator;
    private SimpleSelectPropertyShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->globalValidator = $this->createMock(ValidatorInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->sut = new SimpleSelectPropertyShouldBeValidValidator($this->globalValidator);
        $this->sut->initialize($this->context);
        $this->globalValidator->method('inContext')->with($this->context)->willReturn($this->contextualValidator);
        $this->contextualValidator->method('validate')->willReturn($this->contextualValidator);
        $this->contextualValidator->method('getViolations')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SimpleSelectPropertyShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'simple_select', 'process' => ['type' => 'no']], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new SimpleSelectPropertyShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_have_no_type(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['process' => []], new SimpleSelectPropertyShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_have_bad_type(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['type' => 'auto_number', 'process' => []], new SimpleSelectPropertyShouldBeValid());
    }

    public function test_it_should_build_violation_when_process_is_missing(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.simple_select_property_fields_required',
            ['{{ field }}' => 'process']
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(['type' => 'simple_select', 'attributeCode' => 'color'], new SimpleSelectPropertyShouldBeValid());
    }

    public function test_it_should_build_violation_when_attribute_code_is_missing(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.simple_select_property_fields_required',
            ['{{ field }}' => 'attributeCode']
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(['type' => 'simple_select', 'process' => ['type' => Process::PROCESS_TYPE_NO]], new SimpleSelectPropertyShouldBeValid());
    }

    public function test_it_should_validate_a_property_with_the_correct_parameters(): void
    {
        $process = ['type' => Process::PROCESS_TYPE_NO];
        $structure = ['type' => 'simple_select', 'attributeCode' => 'color', 'process' => $process];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->contextualValidator->expects($this->once())->method('validate')->with($structure, $this->anything())->willReturn($this->contextualValidator);
        $this->sut->validate($structure, new SimpleSelectPropertyShouldBeValid());
    }
}
