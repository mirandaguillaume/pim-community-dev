<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUser;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleShouldBeEditableByUserValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleShouldBeEditableByUserValidatorTest extends TestCase
{
    private IsLocaleEditable|MockObject $isLocaleEditable;
    private ExecutionContext|MockObject $context;
    private LocaleShouldBeEditableByUserValidator $sut;

    protected function setUp(): void
    {
        $this->isLocaleEditable = $this->createMock(IsLocaleEditable::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new LocaleShouldBeEditableByUserValidator($this->isLocaleEditable);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(LocaleShouldBeEditableByUserValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_an_exception_with_a_wrong_constraint(): void
    {
        $command = UpsertProductCommand::createWithIdentifier(userId: 1, productIdentifier: ProductIdentifier::fromIdentifier('foo'), userIntents: []);
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate($command, new Type([]));
    }

    public function test_it_throws_an_exception_with_a_wrong_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(new \stdClass(), new LocaleShouldBeEditableByUser([]));
    }

    public function test_it_validates_when_the_locale_is_editable_by_the_user(): void
    {
        $valueUserIntent = new SetTextValue('a_text', null, 'en_US', 'new value');
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$valueUserIntent]
        ));
        $this->isLocaleEditable->method('forUserId')->with('en_US', 1)->willReturn(true);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }

    public function test_it_adds_a_violation_when_the_locale_is_not_editable_for_the_user(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new LocaleShouldBeEditableByUser();
        $valueUserIntent = new SetTextValue('a_text', null, 'de_DE', 'new value');
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$valueUserIntent]
        ));
        $this->isLocaleEditable->method('forUserId')->with('de_DE', 1)->willReturn(false);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ locale_code }}' => 'de_DE'])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setCode')->with((string) ViolationCode::PERMISSION)->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }

    public function test_it_does_nothing_when_value_intent_does_not_concern_a_locale(): void
    {
        $valueUserIntent = new SetTextValue('a_text', null, null, 'new value');
        $this->context->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(
            userId: 1,
            productIdentifier: ProductIdentifier::fromIdentifier('foo'),
            userIntents: [$valueUserIntent]
        ));
        $this->isLocaleEditable->expects($this->never())->method('forUserId')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($valueUserIntent, new LocaleShouldBeEditableByUser());
    }
}
