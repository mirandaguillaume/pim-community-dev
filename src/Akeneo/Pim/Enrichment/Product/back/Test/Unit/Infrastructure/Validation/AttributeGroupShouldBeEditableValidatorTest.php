<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeEditable;
use Akeneo\Pim\Enrichment\Product\Infrastructure\Validation\AttributeGroupShouldBeEditableValidator;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeGroupShouldBeEditableValidatorTest extends TestCase
{
    private IsAttributeEditable|MockObject $isAttributeEditable;
    private ExecutionContext|MockObject $executionContext;
    private AttributeGroupShouldBeEditableValidator $sut;

    protected function setUp(): void
    {
        $this->isAttributeEditable = $this->createMock(IsAttributeEditable::class);
        $this->executionContext = $this->createMock(ExecutionContext::class);
        $this->sut = new AttributeGroupShouldBeEditableValidator($this->isAttributeEditable);
        $this->sut->initialize($this->executionContext);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
        $this->assertInstanceOf(AttributeGroupShouldBeEditableValidator::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            new SetTextValue('identifier1', null, null, 'foo'),
            new NotBlank(),
        );
    }

    public function test_it_can_only_validate_value_user_intents(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate(
            new \stdClass(),
            new AttributeGroupShouldBeEditable(),
        );
    }

    public function test_it_should_build_a_violation_when_the_attribute_value_is_not_editable(): void
    {
        $constraintViolationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->isAttributeEditable->method('forCode')->with('attributeCode', 1)->willReturn(false);
        $this->executionContext->expects($this->once())->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []));
        $this->executionContext->expects($this->once())->method('buildViolation')->with(
            'pim_enrich.product.validation.upsert.attribute_group_no_access_to_attributes',
            [ '{{ attributeCode }}' => 'attributeCode']
        )->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->method('setCode')->with('5')->willReturn($constraintViolationBuilder);
        $constraintViolationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate(
            new SetTextValue('attributeCode', null, null, 'foo'),
            new AttributeGroupShouldBeEditable()
        );
    }

    public function test_it_should_not_build_any_violation_when_the_attribute_value_is_editable(): void
    {
        $this->isAttributeEditable->method('forCode')->with('attributeCode', 1)->willReturn(true);
        $this->executionContext->expects($this->once())->method('getRoot')->willReturn(UpsertProductCommand::createWithIdentifier(1, ProductIdentifier::fromIdentifier('identifier1'), userIntents: []));
        $this->executionContext->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate(
            new SetTextValue('attributeCode', null, null, 'foo'),
            new AttributeGroupShouldBeEditable()
        );
    }
}
