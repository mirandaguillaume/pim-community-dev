<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Validation;

use Akeneo\Category\Api\Command\UserIntents\SetLabel;
use Akeneo\Category\Api\Command\UserIntents\SetTextArea;
use Akeneo\Category\Api\Command\UserIntents\ValueUserIntent;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\IsTemplateDeactivated;
use Akeneo\Category\Domain\Model\Attribute\AttributeTextArea;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldHaveAnActivatedTemplate;
use Akeneo\Category\Infrastructure\Validation\ValueUserIntentsShouldHaveAnActivatedTemplateValidator;
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
class ValueUserIntentsShouldHaveAnActivatedTemplateValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private GetAttribute|MockObject $getAttribute;
    private IsTemplateDeactivated|MockObject $isTemplateDeactivated;
    private ValueUserIntentsShouldHaveAnActivatedTemplateValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->getAttribute = $this->createMock(GetAttribute::class);
        $this->isTemplateDeactivated = $this->createMock(IsTemplateDeactivated::class);
        $this->sut = new ValueUserIntentsShouldHaveAnActivatedTemplateValidator($this->getAttribute, $this->isTemplateDeactivated);
        $this->sut->initialize($this->context);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(ValueUserIntentsShouldHaveAnActivatedTemplateValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function testItThrowsAnExceptionWithAWrongConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(1, new Type([]));
    }

    public function testItDoesNothingWhenTheAttributeValueDoesNotIncludeValueUserIntent(): void
    {
        $this->context->expects($this->never())->method('buildViolation');
        $this->getAttribute->expects($this->never())->method('byUuids');
        $this->isTemplateDeactivated->expects($this->never())->method('__invoke');
        $this->sut->validate([new SetLabel('en_US', 'The label')], new ValueUserIntentsShouldHaveAnActivatedTemplate());
    }

    public function testItDoesNothingWhenTheAttributeValueIsLinkedToAnActivatedTemplate(): void
    {
        /** @var ValueUserIntent $textAreaSEOMetaDescriptionValue */
        $textAreaSEOMetaDescriptionValue = $this->getValueUserIntents()[0];
        /** @var ValueUserIntent $textAreaSEOKeyWordsValue */
        $textAreaSEOKeyWordsValue = $this->getValueUserIntents()[1];
        $this->context->expects($this->never())->method('buildViolation');
        /** @var AttributeTextArea $textAreaSEOMetaDescriptionAttribute */
        $textAreaSEOMetaDescriptionAttribute = $this->getAttributes()[0];
        $this->getAttribute->expects($this->once())->method('byUuids')->with([$textAreaSEOMetaDescriptionValue->attributeUuid()])->willReturn(AttributeCollection::fromArray([$textAreaSEOMetaDescriptionAttribute]));
        $this->isTemplateDeactivated->expects($this->once())->method('__invoke')->with($textAreaSEOMetaDescriptionAttribute->getTemplateUuid())->willReturn(false);
        $this->sut->validate([
            $textAreaSEOMetaDescriptionValue,
            $textAreaSEOKeyWordsValue,
        ], new ValueUserIntentsShouldHaveAnActivatedTemplate());
    }

    public function testItThrowsAnExceptionWhenTheAttributeValueIsLinkedToADeactivatedTemplate(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        /** @var ValueUserIntent $textAreaSEOMetaDescriptionValue */
        $textAreaSEOMetaDescriptionValue = $this->getValueUserIntents()[0];
        /** @var ValueUserIntent $textAreaSEOKeyWordsValue */
        $textAreaSEOKeyWordsValue = $this->getValueUserIntents()[1];
        $constraint = new ValueUserIntentsShouldHaveAnActivatedTemplate();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationBuilder);
        $violationBuilder->method('setCode')->with('deactivated_template')->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        /** @var AttributeTextArea $textAreaSEOMetaDescriptionAttribute */
        $textAreaSEOMetaDescriptionAttribute = $this->getAttributes()[0];
        $this->getAttribute->expects($this->once())->method('byUuids')->with([$textAreaSEOMetaDescriptionValue->attributeUuid()])->willReturn(AttributeCollection::fromArray([$textAreaSEOMetaDescriptionAttribute]));
        $this->isTemplateDeactivated->expects($this->once())->method('__invoke')->with($textAreaSEOMetaDescriptionAttribute->getTemplateUuid())->willReturn(true);
        $this->sut->validate([
            $textAreaSEOMetaDescriptionValue,
            $textAreaSEOKeyWordsValue,
        ], new ValueUserIntentsShouldHaveAnActivatedTemplate());
    }

    private function getValueUserIntents(): array
    {
        return [
            new SetTextArea(
                'b777dfe6-2518-4d0e-958d-ddb07c81b7b6',
                'seo_meta_description',
                'ecommerce',
                'en_US',
                'SEO meta description',
            ),
            new SetTextArea(
                '1efc3af6-e89c-4281-9bd5-b827d9397cf7',
                'seo_keywords',
                'ecommerce',
                'en_US',
                'SEO keywords',
            ),
        ];
    }

    private function getAttributes(): array
    {
        return [
            AttributeTextArea::create(
                AttributeUuid::fromString('b777dfe6-2518-4d0e-958d-ddb07c81b7b6'),
                new AttributeCode('seo_meta_description'),
                AttributeOrder::fromInteger(11),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                AttributeAdditionalProperties::fromArray([]),
            ),
            AttributeTextArea::create(
                AttributeUuid::fromString('1efc3af6-e89c-4281-9bd5-b827d9397cf7'),
                new AttributeCode('seo_keywords'),
                AttributeOrder::fromInteger(13),
                AttributeIsRequired::fromBoolean(false),
                AttributeIsScopable::fromBoolean(true),
                AttributeIsLocalizable::fromBoolean(true),
                LabelCollection::fromArray(['en_US' => 'SEO keywords']),
                TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                AttributeAdditionalProperties::fromArray([]),
            ),
        ];
    }
}
