<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyPropertyShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FamilyPropertyShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyPropertyShouldBeValidValidatorTest extends TestCase
{
    private ValidatorInterface|MockObject $globalValidator;
    private ExecutionContext|MockObject $context;
    private FamilyPropertyShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->globalValidator = $this->createMock(ValidatorInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new FamilyPropertyShouldBeValidValidator($this->globalValidator);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FamilyPropertyShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'family', 'process' => ['type' => 'no']], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new FamilyPropertyShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_have_no_type(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['process' => []], new FamilyPropertyShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_have_bad_type(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['type' => 'auto_number', 'process' => []], new FamilyPropertyShouldBeValid());
    }

    public function test_it_should_build_violation_when_process_is_missing(): void
    {
        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.family_property_fields_required',
            ['{{ field }}' => 'process']
        );
        $this->sut->validate(['type' => 'family'], new FamilyPropertyShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_have_no_type_under_process(): void
    {
        $structure = ['type' => 'family', 'process' => []];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($structure, new FamilyPropertyShouldBeValid());
    }

    public function test_it_should_validate_a_property_with_a_type_no_process(): void
    {
        $process = ['type' => 'no'];
        $structure = ['type' => 'family', 'process' => $process];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($structure, new FamilyPropertyShouldBeValid());
    }
}
