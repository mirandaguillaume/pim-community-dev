<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\EnabledShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\EnabledShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnabledShouldBeValidValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private EnabledShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new EnabledShouldBeValidValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(EnabledShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'enabled', 'value' => 'abcdef'], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new EnabledShouldBeValid());
    }

    public function test_it_should_not_validate_a_condition_which_is_not_a_enabled(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['type' => 'something_else'], new EnabledShouldBeValid());
    }

    public function test_it_should_build_violation_when_value_is_missing(): void
    {
        $enabledWithoutValue = [
            'type' => 'enabled',
        ];
        $this->context->expects($this->once())->method('buildViolation')->with('validation.identifier_generator.enabled_value_field_required');
        $this->sut->validate($enabledWithoutValue, new EnabledShouldBeValid());
    }

    public function test_it_should_build_violation_when_value_is_not_a_boolean(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $enabledWithoutValue = [
            'type' => 'enabled', 'value' => 'bar',
        ];
        $this->context->expects($this->once())->method('buildViolation')->with('validation.identifier_generator.enabled_boolean_value')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('value')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($enabledWithoutValue, new EnabledShouldBeValid());
    }

    public function test_it_should_not_build_violation_when_enabled_is_valid(): void
    {
        $validEnabled = [
            'type' => 'enabled',
            'value' => true,
        ];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($validEnabled, new EnabledShouldBeValid());
    }
}
