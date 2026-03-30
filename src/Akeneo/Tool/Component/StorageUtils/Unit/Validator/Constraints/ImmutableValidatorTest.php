<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\Immutable;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\ImmutableValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ImmutableValidatorTest extends TestCase
{
    private EntityManager|MockObject $entityManager;
    private ExecutionContextInterface|MockObject $context;
    private ImmutableValidator $sut;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ImmutableValidator($this->entityManager);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ImmutableValidator::class, $this->sut);
    }

    public function test_it_adds_violation_when_an_immutable_property_has_been_modified(): void
    {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $constraint = $this->createMock(Immutable::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $family = new Family();
        $family->setCode('myUpdatedCode');
        $this->entityManager->method('getUnitOfWork')->willReturn($unitOfWork);
        $unitOfWork->method('getOriginalEntityData')->with($family)->willReturn(['code' => 'MyOriginalCode']);
        $this->context->expects($this->once())->method('buildViolation')->with('This property cannot be changed.')->willReturn($violation);
        $violation->method('atPath')->with('code')->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $constraint->properties = ['code'];
        $this->sut->validate($family, $constraint);
    }

    public function test_it_adds_violation_when_an_immutable_reference_data_name_has_been_modified(): void
    {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $constraint = $this->createMock(Immutable::class);
        $violation = $this->createMock(ConstraintViolationBuilderInterface::class);

        $attribute = new Attribute();
        $attribute->setReferenceDataName('myUpdatedReferenceDataName');
        $this->entityManager->method('getUnitOfWork')->willReturn($unitOfWork);
        $unitOfWork->method('getOriginalEntityData')->with($attribute)->willReturn(['properties' => ['reference_data_name' => 'MyOriginalReferenceDataName']]);
        $this->context->expects($this->once())->method('buildViolation')->with('This property cannot be changed.')->willReturn($violation);
        $violation->method('atPath')->with('reference_data_name')->willReturn($violation);
        $violation->expects($this->once())->method('addViolation');
        $constraint->properties = ['reference_data_name'];
        $this->sut->validate($attribute, $constraint);
    }

    public function test_it_does_not_add_violation_when_a_immutable_property_has_not_been_modified(): void
    {
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $constraint = $this->createMock(Immutable::class);

        $family = new Family();
        $family->setCode('MyOriginalCode');
        $this->entityManager->method('getUnitOfWork')->willReturn($unitOfWork);
        $unitOfWork->method('getOriginalEntityData')->with($family)->willReturn(['code' => 'MyOriginalCode']);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything(), $this->anything());
        $constraint->properties = ['code'];
        $this->sut->validate($family, $constraint);
    }
}
