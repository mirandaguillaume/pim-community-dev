<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ConditionsShouldNotContainMultipleCondition;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ConditionsShouldNotContainMultipleConditionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConditionsShouldNotContainMultipleConditionValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private ConditionsShouldNotContainMultipleConditionValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new ConditionsShouldNotContainMultipleConditionValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConditionsShouldNotContainMultipleConditionValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate([], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function test_it_should_not_validate_something_else_than_an_array_of_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate([new \stdClass()], new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function test_it_should_not_validate_something_else_than_an_array_of_property(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate([[]], new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function test_it_should_not_validate_conditions_without_enabled(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $conditions = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'free_text', 'string' => 'ghijkl'],
        ];
        $this->sut->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function test_it_should_build_violation_when_conditions_contains_multiple_enabled(): void
    {
        $conditions = [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => false],
        ];
        $this->context->expects($this->once())->method('buildViolation')->with('validation.identifier_generator.conditions_limit_reached', [
            '{{limit}}' => 1,
            '{{type}}' => 'enabled',
        ]);
        $this->sut->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }

    public function test_it_should_be_valid_when_enabled_is_under_limit(): void
    {
        $conditions = [
            ['type' => 'family', 'IN' => ['shirts']],
            ['type' => 'enabled', 'numberMin' => 3, 'digitsMin' => 2],
        ];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($conditions, new ConditionsShouldNotContainMultipleCondition(['enabled', 'family']));
    }
}
