<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelShouldBeConsistent;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\LocaleAndChannelShouldBeConsistentValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LocaleAndChannelShouldBeConsistentValidatorTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private ChannelExistsWithLocaleInterface|MockObject $channelExistsWithLocale;
    private ExecutionContextInterface|MockObject $context;
    private LocaleAndChannelShouldBeConsistentValidator $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->channelExistsWithLocale = $this->createMock(ChannelExistsWithLocaleInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new LocaleAndChannelShouldBeConsistentValidator($this->getAttributes, $this->channelExistsWithLocale);
        $this->channelExistsWithLocale->method('doesChannelExist')
            ->willReturnCallback(fn (string $channel) => $channel === 'ecommerce');
        $this->channelExistsWithLocale->method('isLocaleActive')
            ->willReturnCallback(fn (string $locale) => in_array($locale, ['en_US', 'fr_FR']));
        $this->channelExistsWithLocale->method('isLocaleBoundToChannel')
            ->willReturnCallback(fn (string $locale, string $channel) => $locale === 'en_US' && $channel === 'ecommerce');
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
        $this->assertInstanceOf(LocaleAndChannelShouldBeConsistentValidator::class, $this->sut);
    }

    public function test_it_throws_an_exception_for_a_wrong_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            [new SetTextValue('name', 'ecommerce', 'en_US', 'My beautiful product')],
            new NotBlank(),
        );
    }

    public function test_it_throws_an_exception_if_the_value_is_not_an_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            new SetTextValue('name', null, null, 'foo bar'),
            new LocaleAndChannelShouldBeConsistent(),
        );
    }

    public function test_it_throws_an_exception_if_one_of_the_values_is_not_a_value_user_intent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            [new SetTextValue('name', null, null, 'foo bar'), new \stdClass()],
            new LocaleAndChannelShouldBeConsistent(),
        );
    }

    public function test_it_does_nothing_if_the_attribute_does_not_exist(): void
    {
        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn(['name' => null]);
        $this->context->expects($this->never())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', 'ecommerce', 'en_US', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_no_channel_code_is_provided_for_a_scopable_attribute(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', true, false),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::NO_CHANNEL_CODE_PROVIDED_FOR_SCOPABLE_ATTRIBUTE,
            [
                            '{{ attributeCode }}' => 'name',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].channelCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', null, null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_a_channel_code_is_provided_for_a_non_scopable_attribute(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', false, false),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::CHANNEL_CODE_PROVIDED_FOR_NON_SCOPABLE_ATTRIBUTE,
            [
                            '{{ attributeCode }}' => 'name',
                            '{{ channelCode }}' => 'ecommerce',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].channelCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', 'ecommerce', null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_the_channel_does_not_exist(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', true, false),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::CHANNEL_DOES_NOT_EXIST,
            [
                            '{{ channelCode }}' => 'mobile',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].channelCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', 'mobile', null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_no_locale_code_is_provided_for_a_localizable_attribute(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', false, true),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::NO_LOCALE_CODE_PROVIDED_FOR_LOCALIZABLE_ATTRIBUTE,
            [
                            '{{ attributeCode }}' => 'name',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].localeCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', null, null, 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_a_locale_code_is_provided_for_a_non_localizable_attribute(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', false, false),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::LOCALE_CODE_PROVIDED_FOR_NON_LOCALIZABLE_ATTRIBUTE,
            [
                            '{{ attributeCode }}' => 'name',
                            '{{ localeCode }}' => 'en_US',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].localeCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', null, 'en_US', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_locale_code_is_not_active(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', false, true),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::LOCALE_IS_NOT_ACTIVE,
            [
                            '{{ localeCode }}' => 'es_ES',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].localeCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', null, 'es_ES', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_locale_is_not_bound_to_the_channel(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', true, true),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::LOCALE_NOT_ACTIVATED_FOR_CHANNEL,
            [
                            '{{ localeCode }}' => 'fr_FR',
                            '{{ channelCode }}' => 'ecommerce',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].localeCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', 'ecommerce', 'fr_FR', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_adds_a_violation_if_locale_is_invalid_for_a_locale_specific_attribute(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->getAttributes->expects($this->once())->method('forCodes')->with(['name'])->willReturn([
                    'name' => $this->getTextAttribute('name', false, true, ['en_US']),
                ]);
        $this->context->expects($this->once())->method('buildViolation')->with(
            LocaleAndChannelShouldBeConsistent::INVALID_LOCALE_CODE_FOR_LOCALE_SPECIFIC_ATTRIBUTE,
            [
                            '{{ attributeCode }}' => 'name',
                            '{{ localeCode }}' => 'fr_FR',
                            '{{ availableLocales }}' => 'en_US',
                        ]
        )->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('atPath')->with('[0].localeCode')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            [new SetTextValue('name', null, 'fr_FR', 'My beautiful product')],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    public function test_it_does_not_add_a_violation_when_scope_and_locale_are_consistent(): void
    {
        $this->getAttributes->expects($this->once())->method('forCodes')->with([
                    'scopable_localizable',
                    'scopable',
                    'localizable',
                    'locale_specific',
                    'simple',
                ])->willReturn([
                    'localizable_scopable' => $this->getTextAttribute('scopable_localizable', true, true),
                    'scopable' => $this->getTextAttribute('scopable', true, false),
                    'localizable' => $this->getTextAttribute('localizable', false, true),
                    'locale_specific' => $this->getTextAttribute('locale_specific', false, true, ['en_US']),
                    'simple' => $this->getTextAttribute('simple', false, false),
                ]);
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(
            [
                        new SetTextValue('scopable_localizable', 'ecommerce', 'en_US', 'My beautiful product'),
                        new SetTextValue('scopable', 'ecommerce', null, 'My beautiful product'),
                        new SetTextValue('localizable', null, 'fr_FR', 'My beautiful product'),
                        new SetTextValue('locale_specific', null, 'en_US', 'My beautiful product'),
                        new SetTextValue('simple', null, null, 'My beautiful product'),
                    ],
            new LocaleAndChannelShouldBeConsistent()
        );
    }

    private function getTextAttribute(
        string $attributeCode,
        bool $scopable,
        bool $localizable,
        array $availableLocaleCodes = []
    ): Attribute {
        return new Attribute(
            $attributeCode,
            'pim_catalog_text',
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'text',
            $availableLocaleCodes
        );
    }
}
