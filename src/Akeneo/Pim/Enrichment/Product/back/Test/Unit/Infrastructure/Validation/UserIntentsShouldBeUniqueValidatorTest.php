<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetPriceValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserIntentsShouldBeUnique;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\UserIntentsShouldBeUniqueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UserIntentsShouldBeUniqueValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private UserIntentsShouldBeUniqueValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new UserIntentsShouldBeUniqueValidator();
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(UserIntentsShouldBeUniqueValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(1, new Type([]));
    }

    public function test_it_throws_an_exception_when_value_intents_collide(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new UserIntentsShouldBeUnique();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ attributeCode }}' => 'a_text'])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate([
                    new SetTextValue('a_text', 'a_channel', 'a_locale', 'foo'),
                    new SetTextValue('another_text', 'a_channel', 'a_locale', 'bar'),
                    new SetTextValue('a_text', 'a_channel', 'a_locale', 'baz'),
                    new SetTextValue('a_text', null, null, 'toto'),
                ], $constraint);
    }

    public function test_it_throws_an_exception_when_price_value_intents_collide(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new UserIntentsShouldBeUnique();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ attributeCode }}' => 'a_price'])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate([
                    new SetPriceValue('a_price', 'a_channel', 'a_locale', new PriceValue('10', 'EUR')),
                    new SetPriceValue('another_price', 'a_channel', 'a_locale', new PriceValue('15', 'EUR')),
                    new SetPriceValue('a_price', 'a_channel', 'a_locale', new PriceValue('15', 'EUR')),
                    new SetPriceValue('a_price', null, null, new PriceValue('20', 'USD')),
                ], $constraint);
    }

    public function test_it_throws_an_exception_when_price_value_is_set_and_clear(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new UserIntentsShouldBeUnique();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ attributeCode }}' => 'a_price'])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate([
                    new SetPriceValue('a_price', 'a_channel', 'a_locale', new PriceValue('10', 'EUR')),
                    new ClearPriceValue('a_price', 'a_channel', 'a_locale', 'EUR'),
                ], $constraint);
    }

    public function test_it_does_nothing_when_the_value_intents_are_distinct(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
                    new SetTextValue('a_text', 'a_channel', 'a_locale', 'foo'),
                    new SetTextValue('a_text', 'a_channel', 'another_locale', 'bar'),
                    new SetTextValue('a_text', 'another_channel', 'a_locale', 'baz'),
                    new SetTextValue('a_text', 'another_channel', 'another_locale', 'toto'),
                ], new UserIntentsShouldBeUnique());
    }

    public function test_it_does_nothing_when_the_price_value_intents_are_distinct(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate([
                    new SetPriceValue('a_price', 'a_channel', 'a_locale', new PriceValue('100', 'EUR')),
                    new SetPriceValue('a_price', 'a_channel', 'a_locale', new PriceValue('120', 'USD')),
                ], new UserIntentsShouldBeUnique());
    }
}
