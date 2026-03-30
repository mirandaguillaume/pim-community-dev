<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Unit\Structure\Component\Validator\Constraints;

use Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirementInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirements;
use Akeneo\Pim\Structure\Component\Validator\Constraints\FamilyRequirementsValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyRequirementsValidatorTest extends TestCase
{
    private AttributeRepositoryInterface|MockObject $attributeRepository;
    private ChannelRepositoryInterface|MockObject $channelRepository;
    private FamilyRequirements|MockObject $minimumRequirements;
    private ExecutionContextInterface|MockObject $context;
    private FamilyRequirementsValidator $sut;

    protected function setUp(): void
    {
        $this->attributeRepository = $this->createMock(AttributeRepositoryInterface::class);
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->minimumRequirements = $this->createMock(FamilyRequirements::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new FamilyRequirementsValidator($this->attributeRepository, $this->channelRepository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf('Symfony\Component\Validator\ConstraintValidatorInterface', $this->sut);
    }

    public function test_it_validates_families_with_identifier_requirements(): void
    {
        $family = $this->createMock(FamilyInterface::class);
        $requirementEcommerce = $this->createMock(AttributeRequirementInterface::class);
        $requirementMobile = $this->createMock(AttributeRequirementInterface::class);

        $requirementEcommerce->method('isRequired')->willReturn(true);
        $requirementMobile->method('isRequired')->willReturn(true);
        $family->method('getAttributeRequirements')->willReturn([$requirementEcommerce, $requirementMobile]);
        $family->method('getAttributeCodes')->willReturn(['sku','ecommerce']);
        $this->attributeRepository->method('getIdentifierCode')->willReturn('sku');
        $requirementEcommerce->method('getAttributeCode')->willReturn('sku');
        $requirementEcommerce->method('getChannelCode')->willReturn('ecommerce');
        $requirementMobile->method('getAttributeCode')->willReturn('sku');
        $requirementMobile->method('getChannelCode')->willReturn('mobile');
        $this->channelRepository->method('getChannelCodes')->willReturn(['ecommerce', 'mobile']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate($family, $this->minimumRequirements);
    }

    public function test_it_validates_families_without_identifier_requirements(): void
    {
        $family = $this->createMock(FamilyInterface::class);
        $requirementEcommerce = $this->createMock(AttributeRequirementInterface::class);
        $requirementMobile = $this->createMock(AttributeRequirementInterface::class);

        $requirementEcommerce->method('isRequired')->willReturn(true);
        $requirementMobile->method('isRequired')->willReturn(true);
        $family->method('getAttributeRequirements')->willReturn([$requirementEcommerce, $requirementMobile]);
        $family->method('getAttributeCodes')->willReturn(['sku','ecommerce']);
        $this->attributeRepository->method('getIdentifierCode')->willReturn('sku');
        $requirementEcommerce->method('getAttributeCode')->willReturn('sku');
        $requirementEcommerce->method('getChannelCode')->willReturn('ecommerce');
        $requirementMobile->method('getAttributeCode')->willReturn('sku');
        $requirementMobile->method('getChannelCode')->willReturn('mobile');
        $this->channelRepository->method('getChannelCodes')->willReturn(['ecommerce', 'mobile', 'print']);
        $family->method('getCode')->willReturn('familyCode');
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything(), $this->anything());
        $this->sut->validate($family, $this->minimumRequirements);
    }

    public function test_it_does_not_validate_family_with_required_attribute_not_present(): void
    {
        $family = $this->createMock(FamilyInterface::class);
        $requirementEcommerce = $this->createMock(AttributeRequirementInterface::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $requirementEcommerce->method('isRequired')->willReturn(true);
        $requirementEcommerce->method('getAttributeCode')->willReturn('color');
        $requirementEcommerce->method('getChannelCode')->willReturn('ecommerce');
        $family->method('getCode')->willReturn('familyCode');
        $family->method('getAttributeRequirements')->willReturn([$requirementEcommerce]);
        $family->method('getAttributeCodes')->willReturn(['sku']);
        $this->channelRepository->method('getChannelCodes')->willReturn(['ecommerce', 'mobile', 'print']);
        $this->context->expects($this->once())->method('buildViolation')->with($this->anything(), $this->anything())->willReturn($violation);
        $violation->method('atPath')->with($this->anything())->willReturn($violation);
        $violation->expects($this->once())->method('addViolation')->with($this->anything());
        $this->sut->validate($family, $this->minimumRequirements);
    }

    public function test_it_does_validate_family_with_attribute_not_required(): void
    {
        $family = $this->createMock(FamilyInterface::class);
        $requirementEcommerce = $this->createMock(AttributeRequirementInterface::class);

        $requirementEcommerce->method('isRequired')->willReturn(false);
        $requirementEcommerce->method('getAttributeCode')->willReturn('color');
        $requirementEcommerce->method('getChannelCode')->willReturn('ecommerce');
        $family->method('getCode')->willReturn('familyCode');
        $family->method('getAttributeRequirements')->willReturn([$requirementEcommerce]);
        $family->method('getAttributeCodes')->willReturn(['sku']);
        $this->channelRepository->method('getChannelCodes')->willReturn(['ecommerce', 'mobile', 'print']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything(), $this->anything());
        $this->sut->validate($family, $this->minimumRequirements);
    }
}
