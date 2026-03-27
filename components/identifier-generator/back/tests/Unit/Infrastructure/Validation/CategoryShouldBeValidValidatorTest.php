<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\CategoryShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryShouldBeValidValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $globalValidator;
    private ExecutionContext|MockObject $context;
    private ContextualValidatorInterface|MockObject $contextualValidator;
    private CategoryShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->globalValidator = $this->createMock(ValidatorInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->contextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->sut = new CategoryShouldBeValidValidator($this->globalValidator);
        $this->sut->initialize($this->context);
        $this->globalValidator->method('inContext')->with($this->context)->willReturn($this->contextualValidator);
        $this->contextualValidator->method('validate')->willReturn($this->contextualValidator);
        $this->contextualValidator->method('getViolations')->willReturn(new ConstraintViolationList());
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CategoryShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank());
    }

    public function test_it_should_not_validate_if_condition_is_not_an_array(): void
    {
        $condition = 'foo';
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate($condition, new CategoryShouldBeValid());
    }

    public function test_it_should_not_validate_other_conditions(): void
    {
        $condition = ['type' => 'foo'];
        $this->contextualValidator->expects($this->never())->method('validate');
        $this->sut->validate($condition, new CategoryShouldBeValid());
    }

    public function test_it_should_only_validate_condition_keys(): void
    {
        $condition = ['type' => 'category', 'foo' => 'bar'];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(1))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;

                return $this->contextualValidator;
            });
        $this->sut->validate($condition, new CategoryShouldBeValid());

        // validateConditionKeys: Collection
        $this->assertInstanceOf(Collection::class, $constraintArgs[0]);
    }

    public function test_it_should_validate_condition_keys_without_value(): void
    {
        $condition = ['type' => 'category', 'operator' => 'CLASSIFIED'];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(2))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;

                return $this->contextualValidator;
            });
        $this->sut->validate($condition, new CategoryShouldBeValid());

        // Call 1: validateConditionKeys (Collection)
        $this->assertInstanceOf(Collection::class, $constraintArgs[0]);
        // Call 2: validateValueIsUndefined (Collection)
        $this->assertInstanceOf(Collection::class, $constraintArgs[1]);
    }

    public function test_it_should_validate_condition_keys_with_value_and_categories(): void
    {
        $condition = ['type' => 'category', 'operator' => 'IN', 'value' => ['shirts']];
        $constraintArgs = [];
        $this->contextualValidator->expects($this->exactly(2))->method('validate')
            ->with($condition, $this->anything())
            ->willReturnCallback(function ($value, $constraints) use (&$constraintArgs) {
                $constraintArgs[] = $constraints;

                return $this->contextualValidator;
            });
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($condition, new CategoryShouldBeValid());

        // Call 1: validateConditionKeys (Collection)
        $this->assertInstanceOf(Collection::class, $constraintArgs[0]);
        // Call 2: validateValueField (Collection)
        $this->assertInstanceOf(Collection::class, $constraintArgs[1]);
    }
}
