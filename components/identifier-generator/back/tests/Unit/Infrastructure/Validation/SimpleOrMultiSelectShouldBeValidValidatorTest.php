<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ScopeAndLocaleShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SelectOptionShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleOrMultiSelectShouldBeValidValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $globalValidator;
    private ExecutionContext|MockObject $context;
    private ContextualValidatorInterface|MockObject $contextualValidator;
    private SimpleOrMultiSelectShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->globalValidator = $this->createMock(ValidatorInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->sut = new SimpleOrMultiSelectShouldBeValidValidator($this->globalValidator);
        $this->sut->initialize($this->context);
        $this->globalValidator->method('inContext')->with($this->context)->willReturn($this->contextualValidator);
        $this->contextualValidator->method('validate')->willReturn($this->contextualValidator);
        $this->contextualValidator->method('getViolations')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SimpleOrMultiSelectShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new NotBlank()
        );
    }

    public function test_it_should_not_validate_if_condition_is_not_an_array(): void
    {
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate('foo', new SimpleOrMultiSelectShouldBeValid());
    }

    public function test_it_should_not_validate_other_conditions(): void
    {
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate(['type' => 'foo'], new SimpleOrMultiSelectShouldBeValid());
    }

    public function test_it_should_only_validate_condition_keys_without_operator(): void
    {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color'];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(1))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;
                return $this->contextualValidator;
            });
        $this->sut->validate($condition, new SimpleOrMultiSelectShouldBeValid());

        // validateConditionKeys: [Collection, ScopeAndLocaleShouldBeValid]
        $this->assertIsArray($constraintArgs[0]);
        $this->assertInstanceOf(Collection::class, $constraintArgs[0][0]);
        $this->assertInstanceOf(ScopeAndLocaleShouldBeValid::class, $constraintArgs[0][1]);
    }

    public function test_it_should_validate_condition_keys_without_value(): void
    {
        $condition = ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(2))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;
                return $this->contextualValidator;
            });
        $this->sut->validate($condition, new SimpleOrMultiSelectShouldBeValid());

        // Call 1: validateConditionKeys [Collection, ScopeAndLocaleShouldBeValid]
        $this->assertIsArray($constraintArgs[0]);
        $this->assertInstanceOf(Collection::class, $constraintArgs[0][0]);
        $this->assertInstanceOf(ScopeAndLocaleShouldBeValid::class, $constraintArgs[0][1]);
        // Call 2: validateValueIsUndefined (Collection)
        $this->assertInstanceOf(Collection::class, $constraintArgs[1]);
    }

    public function test_it_should_validate_condition_keys_with_value_and_families(): void
    {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'red']];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(2))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;
                return $this->contextualValidator;
            });
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new SimpleOrMultiSelectShouldBeValid());

        // Call 1: validateConditionKeys [Collection, ScopeAndLocaleShouldBeValid]
        $this->assertIsArray($constraintArgs[0]);
        $this->assertInstanceOf(Collection::class, $constraintArgs[0][0]);
        $this->assertInstanceOf(ScopeAndLocaleShouldBeValid::class, $constraintArgs[0][1]);
        // Call 2: validateValueField [Collection, SelectOptionShouldExist]
        $this->assertIsArray($constraintArgs[1]);
        $this->assertInstanceOf(Collection::class, $constraintArgs[1][0]);
        $this->assertInstanceOf(SelectOptionShouldExist::class, $constraintArgs[1][1]);
    }

    public function test_it_should_validate_multi_select(): void
    {
        $condition = ['type' => 'multi_select', 'operator' => 'EMPTY', 'value' => ['option_a', 'option_b']];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(2))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;
                return $this->contextualValidator;
            });
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new SimpleOrMultiSelectShouldBeValid());

        // Call 1: validateConditionKeys [Collection, ScopeAndLocaleShouldBeValid]
        $this->assertIsArray($constraintArgs[0]);
        $this->assertInstanceOf(Collection::class, $constraintArgs[0][0]);
        $this->assertInstanceOf(ScopeAndLocaleShouldBeValid::class, $constraintArgs[0][1]);
        // Call 2: validateValueIsUndefined (Collection) -- EMPTY operator
        $this->assertInstanceOf(Collection::class, $constraintArgs[1]);
    }
}
