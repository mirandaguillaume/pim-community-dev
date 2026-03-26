<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\GetChannelCodeWithLocaleCodesInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ScopeAndLocaleShouldBeValid;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\ScopeAndLocaleShouldBeValidValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeAndLocaleShouldBeValidValidatorTest extends TestCase
{
    private GetAttributes|MockObject $getAttributes;
    private GetChannelCodeWithLocaleCodesInterface|MockObject $getChannelCodeWithLocaleCodes;
    private ExecutionContext|MockObject $context;
    private ScopeAndLocaleShouldBeValidValidator $sut;

    protected function setUp(): void
    {
        $this->getAttributes = $this->createMock(GetAttributes::class);
        $this->getChannelCodeWithLocaleCodes = $this->createMock(GetChannelCodeWithLocaleCodesInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new ScopeAndLocaleShouldBeValidValidator($this->getAttributes, $this->getChannelCodeWithLocaleCodes);
        $this->sut->initialize($this->context);
        $this->getChannelCodeWithLocaleCodes->method('findAll')->willReturn([
            ['channelCode' => 'ecommerce', 'localeCodes' => ['en_US']],
            ['channelCode' => 'website', 'localeCodes' => ['fr_FR']],
        ]);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ScopeAndLocaleShouldBeValidValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new NotBlank()
        );
    }

    public function test_it_should_not_validate_something_else_than_an_array(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(new \stdClass(), new ScopeAndLocaleShouldBeValid());
    }

    public function test_it_should_not_validate_if_there_are_no_attribute_code(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_not_validate_if_attribute_does_not_exist(): void
    {
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn(null);
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_scope_is_missing_for_scopable_attribute(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with($this->anything(), ['{{ attributeCode }}' => 'color'])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[scope]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(scopable: true));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_scope_is_set_for_non_scopable_attribute(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with('This field was not expected.')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[scope]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(scopable: false));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'scope' => 'ecommerce'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_scope_does_not_exist(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.unknown_scope',
            ['{{ scopeCode }}' => 'unknown']
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[scope]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(scopable: true));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'scope' => 'unknown'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_locale_is_missing_for_localizable_attribute(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with($this->anything(), ['{{ attributeCode }}' => 'color'])->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[locale]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(localizable: true));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_locale_is_set_for_non_localizable_attribute(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with('This field was not expected.')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[locale]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(localizable: false));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'en_US'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_locale_does_not_exist(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.unknown_locale',
            ['{{ localeCode }}' => 'unknown']
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[locale]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(localizable: true));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'unknown'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    public function test_it_should_build_violation_when_locale_does_not_belong_to_channel(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context->expects($this->once())->method('buildViolation')->with(
            'validation.identifier_generator.inactive_locale',
            ['{{ localeCode }}' => 'fr_FR', '{{ scopeCode }}' => 'ecommerce']
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('atPath')->with('[locale]')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->getAttributes->expects($this->once())->method('forCode')->with('color')->willReturn($this->getColorAttribute(localizable: true, scopable: true));
        $this->sut->validate(
            ['type' => 'simple_select', 'operator' => 'EMPTY', 'attributeCode' => 'color', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            new ScopeAndLocaleShouldBeValid()
        );
    }

    private function getColorAttribute(
        bool $scopable = false,
        bool $localizable = false
    ): Attribute {
        return new Attribute(
            'color',
            'pim_catalog_simpleselect',
            [],
            $localizable,
            $scopable,
            null,
            null,
            null,
            'pim_catalog_simpleselect',
            []
        );
    }
}
