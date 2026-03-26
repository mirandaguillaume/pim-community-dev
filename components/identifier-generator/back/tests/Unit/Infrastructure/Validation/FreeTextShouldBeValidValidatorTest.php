<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\FreeTextShouldBeValidValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextShouldBeValidValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private FreeTextShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new FreeTextShouldBeValidValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(FreeTextShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['type' => 'free_text', 'string' => 'abcdef'], new NotBlank());
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new FreeTextShouldBeValid());
    }

    public function test_it_should_not_validate_a_property_which_is_not_a_free_text(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(['type' => 'auto_number', 'numberMin' => 2, 'digitsMin' => 3], new FreeTextShouldBeValid());
    }

    public function test_it_should_build_violation_when_free_text_is_invalid(): void
    {
        $freeTextWithoutString = [
                    'type' => 'free_text',
                ];
        $this->context->expects($this->once())->method('buildViolation')->with('validation.identifier_generator.free_text_string_field_required');
        $this->sut->validate($freeTextWithoutString, new FreeTextShouldBeValid());
    }

    public function test_it_should_not_build_violation_when_free_text_is_valid(): void
    {
        $freeTextWithoutString = [
                    'type' => 'free_text',
                    'string' => 'abcdef',
                ];
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($freeTextWithoutString, new FreeTextShouldBeValid());
    }
}
