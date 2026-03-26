<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\StructureShouldNotContainMultipleAutoNumberValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StructureShouldNotContainMultipleAutoNumberValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private StructureShouldNotContainMultipleAutoNumberValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new StructureShouldNotContainMultipleAutoNumberValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(StructureShouldNotContainMultipleAutoNumberValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate([], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new StructureShouldNotContainMultipleAutoNumber());
    }

    public function test_it_should_not_validate_something_else_than_an_array_of_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate([new \stdClass()], new StructureShouldNotContainMultipleAutoNumber());
    }

    public function test_it_should_not_validate_something_else_than_an_array_of_property(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate([[]], new StructureShouldNotContainMultipleAutoNumber());
    }

    public function test_it_should_not_validate_a_structure_without_auto_number(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'free_text', 'string' => 'ghijkl'],
        ];
        $this->sut->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }

    public function test_it_should_build_violation_when_structure_contains_multiple_auto_number(): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 4],
        ];
        $this->context->expects($this->once())->method('buildViolation')->with('validation.identifier_generator.structure_auto_number_limit_reached', [
            '{{limit}}' => 1,
        ]);
        $this->sut->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }

    public function test_it_should_be_valid_when_auto_number_is_under_limit(): void
    {
        $structure = [
            ['type' => 'free_text', 'string' => 'abcdef'],
            ['type' => 'auto_number', 'numberMin' => 3, 'digitsMin' => 2],
        ];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($structure, new StructureShouldNotContainMultipleAutoNumber());
    }
}
