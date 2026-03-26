<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SelectOptionShouldExist;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\SelectOptionShouldExistValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectOptionShouldExistValidatorTest extends TestCase
{
    private GetExistingAttributeOptionsWithValues|MockObject $getExistingAttributeOptionsWithValues;
    private ExecutionContext|MockObject $context;
    private SelectOptionShouldExistValidator $sut;

    protected function setUp(): void
    {
        $this->getExistingAttributeOptionsWithValues = $this->createMock(GetExistingAttributeOptionsWithValues::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new SelectOptionShouldExistValidator($this->getExistingAttributeOptionsWithValues);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(SelectOptionShouldExistValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'red']
                    ], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new SelectOptionShouldExist());
    }

    public function test_it_should_not_validate_if_attribute_code_is_missing(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
                    ['type' => 'simple_select', 'operator' => 'IN', 'value' => ['green', 'red']],
                    new SelectOptionShouldExist()
                );
    }

    public function test_it_should_not_validate_if_value_is_missing(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
                    ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN'],
                    new SelectOptionShouldExist()
                );
    }

    public function test_it_should_not_validate_if_value_is_not_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
                    ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => 'green'],
                    new SelectOptionShouldExist()
                );
    }

    public function test_it_should_not_validate_if_value_is_empty(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
                    ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => []],
                    new SelectOptionShouldExist()
                );
    }

    public function test_it_should_not_validate_if_value_is_not_an_array_of_strings(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
                    ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 0]],
                    new SelectOptionShouldExist()
                );
    }

    public function test_it_should_add_violation_if_codes_do_not_exist(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with($this->anything(), [
                    '{{ attributeCode }}' => 'color',
                    '{{ optionCodes }}' => '"unknown1", "unknown2"',
                ])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[value]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getExistingAttributeOptionsWithValues->expects($this->once())->method('fromAttributeCodeAndOptionCodes')->with([
                    'color.green',
                    'color.unknown1',
                    'color.red',
                    'color.unknown2',
                ])->willReturn([
                    'color.green' => ['en_US' => 'Green'],
                    'color.red' => ['en_US' => 'Red'],
                ]);
        $this->sut->validate(
                    ['type' => 'simple_select', 'attributeCode' => 'color', 'operator' => 'IN', 'value' => ['green', 'unknown1', 'red', 'unknown2']],
                    new SelectOptionShouldExist()
                );
    }
}
