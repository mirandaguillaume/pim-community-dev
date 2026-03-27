<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldBeUnique;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldBeUniqueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueUserIntentsShouldBeUniqueValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private ValueUserIntentsShouldBeUniqueValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new ValueUserIntentsShouldBeUniqueValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ValueUserIntentsShouldBeUniqueValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(1, new Type([]));
    }

    public function test_it_throws_an_exception_when_value_is_not_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('not_an_array', new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_with_empty_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_when_the_value_intents_are_distinct(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetLabel('fr_FR', 'libelle'),
            new SetLabel('en_US', 'label'),
            new SetTextArea('uuid', 'code', 'ecommerce', 'en_US', 'value'),
            new SetTextArea('uuid', 'title', 'ecommerce', 'en_US', 'Title'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_when_only_set_labels(): void
    {
        // SetLabel is not a subclass of ValueUserIntent, so no duplication check
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetLabel('en_US', 'label1'),
            new SetLabel('en_US', 'label2'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_when_same_attribute_different_channel(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetTextArea('uuid', 'same_code', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('uuid', 'same_code', 'mobile', 'en_US', 'value2'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_when_same_attribute_different_locale(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetTextArea('uuid', 'same_code', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('uuid', 'same_code', 'ecommerce', 'fr_FR', 'value2'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_throws_an_exception_when_the_value_intents_are_not_distinct(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ValueUserIntentsShouldBeUnique();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ attributeCode }}' => 'same_attribute_code'])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate([
            new SetLabel('locale', 'libelle'),
            new SetLabel('locale', 'label'),
            new SetTextArea('uuid', 'same_attribute_code', 'ecommerce', 'en_US', 'value'),
            new SetTextArea('uuid-uuid', 'title', 'ecommerce', 'en_US', 'Title'),
            new SetTextArea('uuid', 'same_attribute_code', 'ecommerce', 'en_US', 'other value'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_it_builds_violation_with_correct_attribute_code(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new ValueUserIntentsShouldBeUnique();

        $this->context->expects($this->once())->method('buildViolation')
            ->with($constraint->message, ['{{ attributeCode }}' => 'description'])
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->sut->validate([
            new SetTextArea('uuid1', 'description', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('uuid1', 'description', 'ecommerce', 'en_US', 'value2'),
        ], $constraint);
    }

    public function test_it_throws_when_value_contains_non_user_intent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(['not_a_user_intent'], new ValueUserIntentsShouldBeUnique());
    }

    public function test_duplicate_detection_distinguishes_by_attribute_uuid(): void
    {
        // Two intents with same code but DIFFERENT uuid should NOT be duplicates
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetTextArea('uuid1', 'same_code', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('uuid2', 'same_code', 'ecommerce', 'en_US', 'value2'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_duplicate_detection_uses_separator_in_identifier(): void
    {
        // Two intents with same code+uuid but same channel+locale should trigger violation
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraint = new ValueUserIntentsShouldBeUnique();

        $this->context->expects($this->once())->method('buildViolation')
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        // Must use the same uuid AND code to trigger duplicate
        $this->sut->validate([
            new SetTextArea('same_uuid', 'attr_code', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('same_uuid', 'attr_code', 'ecommerce', 'en_US', 'value2'),
        ], $constraint);
    }

    public function test_intents_with_null_channel_are_ignored_for_uniqueness(): void
    {
        // SetTextArea with null channel should be filtered out (not checked for uniqueness)
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetTextArea('uuid', 'code', null, 'en_US', 'value1'),
            new SetTextArea('uuid', 'code', null, 'en_US', 'value2'),
        ], new ValueUserIntentsShouldBeUnique());
    }

    public function test_intents_with_null_locale_are_ignored_for_uniqueness(): void
    {
        // SetTextArea with null locale should be filtered out
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
            new SetTextArea('uuid', 'code', 'ecommerce', null, 'value1'),
            new SetTextArea('uuid', 'code', 'ecommerce', null, 'value2'),
        ], new ValueUserIntentsShouldBeUnique());
    }
}
