<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyShouldBeValidValidator;
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
class FamilyShouldBeValidValidatorSpec extends ObjectBehavior
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
        $this->shouldHaveType(FamilyShouldBeValidValidator::class);
    }

    public function it_can_only_validate_the_right_constraint(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('validate', [['type' => 'enabled', 'operator' => 'IN', 'value' => ['shirts']], new NotBlank()]);
    }

    public function it_should_not_validate_if_condition_is_not_an_array(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = 'foo';
        $contextualValidator->validate(Argument::cetera())->shouldNotBeCalled();
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_not_validate_other_conditions(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = ['type' => 'foo'];

        $contextualValidator->validate(Argument::cetera())->shouldNotBeCalled();
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_only_validate_condition_keys(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = ['type' => 'family', 'foo' => 'bar'];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(1);
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_validate_condition_keys_without_value(
        ContextualValidatorInterface $contextualValidator,
    ): void {
        $condition = ['type' => 'family', 'operator' => 'EMPTY'];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(2);
        $this->validate($condition, new FamilyShouldBeValid());
    }

    public function it_should_validate_condition_keys_with_value_and_families(
        ContextualValidatorInterface $contextualValidator,
        ExecutionContext $context,
    ): void {
        $condition = ['type' => 'family', 'operator' => 'IN', 'value' => ['shirts']];

        $contextualValidator->validate($condition, Argument::any())->willReturn($contextualValidator)->shouldBeCalledTimes(2);
        $context->buildViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($condition, new FamilyShouldBeValid());
    }
}
