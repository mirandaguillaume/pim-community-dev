<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Unit\Infrastructure\Validation;

use Akeneo\Category\Application\Command\AddAttributeCommand;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\Validation\AttributeCodeShouldBeUniqueInTheTemplate;
use Akeneo\Category\Infrastructure\Validation\AttributeCodeShouldBeUniqueInTheTemplateValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeCodeShouldBeUniqueInTheTemplateValidatorTest extends TestCase
{
    private ExecutionContext|MockObject $context;
    private GetAttribute|MockObject $getAttribute;
    private AttributeCodeShouldBeUniqueInTheTemplateValidator $sut;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContext::class);
        $this->getAttribute = $this->createMock(GetAttribute::class);
        $this->sut = new AttributeCodeShouldBeUniqueInTheTemplateValidator($this->getAttribute);
        $this->sut->initialize($this->context);
    }

    public function testItIsInitializable(): void
    {
        $this->assertInstanceOf(AttributeCodeShouldBeUniqueInTheTemplateValidator::class, $this->sut);
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function testItThrowsAnExceptionWithAWrongConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(1, new Type([]));
    }

    public function testItDoesNothingWhenThereIsNoAttributeInTheTemplate(): void
    {
        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];
        $this->context->method('getObject')->willReturn(AddAttributeCommand::create(
            code: 'other_attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: $templateUuid->getValue(),
            locale: 'en_US',
            label: 'The attribute',
        ));
        $this->context->expects($this->never())->method('buildViolation');
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->with($templateUuid)->willReturn(AttributeCollection::fromArray([]));
        $this->sut->validate('other_attribute_code', new AttributeCodeShouldBeUniqueInTheTemplate());
    }

    public function testItDoesNothingWhenTheAttributeCodeIsUniqueInTheTemplate(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];
        $this->context->method('getObject')->willReturn(AddAttributeCommand::create(
            code: 'other_attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: $templateUuid->getValue(),
            locale: 'en_US',
            label: 'The attribute',
        ));
        $this->context->expects($this->never())->method('buildViolation');
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->with($templateUuid)->willReturn(AttributeCollection::fromArray([$this->getData()['attribute']]));
        $this->sut->validate('other_attribute_code', new AttributeCodeShouldBeUniqueInTheTemplate());
    }

    public function testItThrowsAnExceptionWhenTheAttributeCodeIsNotUniqueInTheTemplate(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        /** @var TemplateUuid $templateUuid */
        $templateUuid = $this->getData()['templateUuid'];
        $this->context->method('getObject')->willReturn(AddAttributeCommand::create(
            code: 'same_attribute_code',
            type: 'text',
            isScopable: true,
            isLocalizable: true,
            templateUuid: $templateUuid->getValue(),
            locale: 'en_US',
            label: 'The attribute',
        ));
        $constraint = new AttributeCodeShouldBeUniqueInTheTemplate();
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message, ['{{ attributeCode }}' => 'same_attribute_code'])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->getAttribute->expects($this->once())->method('byTemplateUuid')->with($templateUuid)->willReturn(AttributeCollection::fromArray([$this->getData()['attribute']]));
        $this->sut->validate('same_attribute_code', $constraint);
    }

    private function getData(): array
    {
        return [
            'templateUuid' => TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            'attribute' => Attribute::fromType(
                type: new AttributeType(AttributeType::TEXT),
                uuid: AttributeUuid::fromString('b777dfe6-2518-4d0e-958d-ddb07c81b7b6'),
                code: new AttributeCode('same_attribute_code'),
                order: AttributeOrder::fromInteger(1),
                isRequired: AttributeIsRequired::fromBoolean(false),
                isScopable: AttributeIsScopable::fromBoolean(true),
                isLocalizable: AttributeIsLocalizable::fromBoolean(true),
                labelCollection: LabelCollection::fromArray(['en_US' => 'SEO meta description']),
                templateUuid: TemplateUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
                additionalProperties: AttributeAdditionalProperties::fromArray([]),
            ),
        ];
    }
}
