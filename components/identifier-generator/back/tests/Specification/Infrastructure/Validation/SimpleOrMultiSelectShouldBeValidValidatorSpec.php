<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SimpleOrMultiSelectShouldBeValidValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleOrMultiSelectShouldBeValidValidatorSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $globalValidator,
        ExecutionContext $context,
        ContextualValidatorInterface $contextualValidator
    ): void
    {
        $this->beConstructedWith($globalValidator);
        $this->initialize($context);

        $globalValidator->inContext($context)->willReturn($contextualValidator);
        $contextualValidator->validate(Argument::cetera())->willReturn($contextualValidator);
        $contextualValidator->getViolations()->willReturn(new ConstraintViolationList());
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(SimpleOrMultiSelectShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [
                ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
                new NotBlank()
            ]);
    }

    public function it_should_not_validate_if_condition_is_not_an_array(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $contextualValidator->validate(Argument::cetera())->shouldNotBeCalled();
        $this->validate('foo', new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_not_validate_other_conditions(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $contextualValidator->validate(Argument::cetera())->shouldNotBeCalled();
        $this->validate(['type' => 'foo'], new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_only_validate_condition_keys_without_operator(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color'];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(1);
        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_condition_keys_without_value(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(2);
        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_condition_keys_with_value_and_families(
        ContextualValidatorInterface $contextualValidator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'red']];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }

    public function it_should_validate_multi_select(
        ContextualValidatorInterface $contextualValidator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'multi_select', 'operator' => 'EMPTY', 'value' => ['option_a', 'option_b']];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new SimpleOrMultiSelectShouldBeValid());
    }
}
