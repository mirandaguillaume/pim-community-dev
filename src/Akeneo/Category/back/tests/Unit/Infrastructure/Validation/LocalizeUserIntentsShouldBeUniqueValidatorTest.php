<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\LocalizeUserIntent;
use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\UserIntent;
use Akeneo\Category\Infrastructure\Validation\LocalizeUserIntentsShouldBeUnique;
use Akeneo\Category\Infrastructure\Validation\LocalizeUserIntentsShouldBeUniqueValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocalizeUserIntentsShouldBeUniqueValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private LocalizeUserIntentsShouldBeUniqueValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new LocalizeUserIntentsShouldBeUniqueValidator();
        $this->sut->initialize($this->context);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(LocalizeUserIntentsShouldBeUniqueValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function testItThrowsAnExceptionWithAWrongConstraint(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate([new SetLabel('en_US', 'socks')], new Type([]));
    }

    public function testItThrowsAnExceptionWhenValueIsNotArray(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('not_an_array', new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItThrowsAnExceptionWhenValueContainsSomethingElseThanAUserIntent(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate([new SetLabel('en_US', 'socks'), new \stdClass()], new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItDoesNothingWithEmptyArray(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->sut->validate([], new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItDoesNothingWhenTheLocalizedIntentsTargetDistinctLocales(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->sut->validate([
            new SetLabel('en_US', 'socks'),
            new SetLabel('fr_FR', 'chaussettes'),
            new SetLabel('de_DE', 'socken'),
        ], new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItIgnoresIntentsThatAreNotLocalized(): void
    {
        // SetTextArea does not implement LocalizeUserIntent: duplicating it must not raise a violation here.
        $this->context->expects($this->never())->method('buildViolation');

        $this->sut->validate([
            new SetTextArea('uuid', 'description', 'ecommerce', 'en_US', 'value1'),
            new SetTextArea('uuid', 'description', 'ecommerce', 'en_US', 'value2'),
        ], new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItBuildsAViolationWhenTwoIntentsOfTheSameClassShareTheSameLocale(): void
    {
        $constraint = new LocalizeUserIntentsShouldBeUnique();
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message, ['{{ locale }}' => 'en_US'])
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->sut->validate([
            new SetLabel('en_US', 'socks'),
            new SetLabel('fr_FR', 'chaussettes'),
            new SetLabel('en_US', 'other label'),
        ], $constraint);
    }

    public function testItBuildsOneViolationPerExtraDuplicate(): void
    {
        $constraint = new LocalizeUserIntentsShouldBeUnique();
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->exactly(2))
            ->method('buildViolation')
            ->with($constraint->message, ['{{ locale }}' => 'en_US'])
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->exactly(2))->method('addViolation');

        $this->sut->validate([
            new SetLabel('en_US', 'first'),
            new SetLabel('en_US', 'second'),
            new SetLabel('en_US', 'third'),
        ], $constraint);
    }

    public function testItDoesNotConsiderIntentsOfDifferentClassesAsDuplicates(): void
    {
        // Uniqueness is scoped per intent class: another localized intent on the same locale is legit.
        $this->context->expects($this->never())->method('buildViolation');

        $this->sut->validate([
            new SetLabel('en_US', 'socks'),
            $this->localizedIntent('en_US'),
        ], new LocalizeUserIntentsShouldBeUnique());
    }

    public function testItFallsBackOnAllLocalesPlaceholderWhenTheLocaleIsNull(): void
    {
        $constraint = new LocalizeUserIntentsShouldBeUnique();
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message, ['{{ locale }}' => '<all_locales>'])
            ->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');

        $this->sut->validate([
            $this->localizedIntent(null),
            $this->localizedIntent(null),
        ], $constraint);
    }

    public function testItDoesNotConflateANullLocaleWithARealOne(): void
    {
        $this->context->expects($this->never())->method('buildViolation');

        $this->sut->validate([
            $this->localizedIntent(null),
            $this->localizedIntent('en_US'),
        ], new LocalizeUserIntentsShouldBeUnique());
    }

    /**
     * Every call returns an instance of the SAME anonymous class, so two instances share `::class`
     * and are compared against each other by the validator — while remaining distinct from SetLabel.
     */
    private function localizedIntent(?string $localeCode): UserIntent
    {
        return new class ($localeCode) implements UserIntent, LocalizeUserIntent {
            public function __construct(private readonly ?string $localeCode) {}

            public function localeCode(): ?string
            {
                return $this->localeCode;
            }
        };
    }
}
