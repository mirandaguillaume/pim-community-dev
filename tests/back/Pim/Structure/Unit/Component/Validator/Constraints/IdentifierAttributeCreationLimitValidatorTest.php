<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimit;
use Akeneo\Pim\Structure\Component\Validator\Constraints\IdentifierAttributeCreationLimitValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierAttributeCreationLimitValidatorTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $repository;
    private ExecutionContext|MockObject $context;
    private IdentifierAttributeCreationLimitValidator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AttributeRepositoryInterface::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new IdentifierAttributeCreationLimitValidator($this->repository, 5);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IdentifierAttributeCreationLimitValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_can_only_validate_the_right_constraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('code', new NotBlank());
    }

    public function test_it_can_only_validate_an_attribute(): void
    {
        $this->repository->expects($this->never())->method('getAttributeCodesByType')->with($this->anything());
        $this->sut->validate(new \stdClass(), new IdentifierAttributeCreationLimit());
    }

    public function test_it_can_only_validate_new_attribute_value(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getId')->willReturn(1);
        $this->repository->expects($this->never())->method('getAttributeCodesByType')->with($this->anything());
        $this->sut->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function test_it_only_validates_identifier_attributes(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getId')->willReturn(null);
        $attribute->method('getType')->willReturn(AttributeTypes::BOOLEAN);
        $this->repository->expects($this->never())->method('getAttributeCodesByType')->with($this->anything());
        $this->sut->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function test_it_should_build_a_violation_when_identifier_attribute_limit_is_reached(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getId')->willReturn(null);
        $attribute->method('getType')->willReturn(AttributeTypes::IDENTIFIER);
        $this->repository->expects($this->once())->method('getAttributeCodesByType')->with(AttributeTypes::IDENTIFIER)->willReturn(['id_1', 'id_2', 'id_3', 'id_4', 'id_5']);
        $this->context->expects($this->once())->method('buildViolation')->with('pim_catalog.constraint.identifier_attribute_limit_reached', ['{{limit}}' => 5])->willReturn($violationBuilder);
        $violationBuilder->expects($this->once())->method('addViolation');
        $this->sut->validate($attribute, new IdentifierAttributeCreationLimit());
    }

    public function test_it_should_be_valid_when_identifier_attribute_is_under_limit(): void
    {
        $attribute = $this->createMock(AttributeInterface::class);

        $attribute->method('getId')->willReturn(null);
        $attribute->method('getType')->willReturn(AttributeTypes::IDENTIFIER);
        $this->repository->expects($this->once())->method('getAttributeCodesByType')->with(AttributeTypes::IDENTIFIER)->willReturn(['id_1', 'id_2']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($attribute, new IdentifierAttributeCreationLimit());
    }
}
