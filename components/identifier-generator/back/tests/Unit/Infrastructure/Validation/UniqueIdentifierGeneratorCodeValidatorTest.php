<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Unit\Infrastructure\Validation;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\UniqueIdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Validation\UniqueIdentifierGeneratorCodeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueIdentifierGeneratorCodeValidatorTest extends TestCase
{
    private IdentifierGeneratorRepository|MockObject $repository;
    private ExecutionContext|MockObject $context;
    private UniqueIdentifierGeneratorCodeValidator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(IdentifierGeneratorRepository::class);
        $this->context = $this->createMock(ExecutionContext::class);
        $this->sut = new UniqueIdentifierGeneratorCodeValidator($this->repository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
        $this->assertInstanceOf(UniqueIdentifierGeneratorCodeValidator::class, $this->sut);
    }

    public function test_it_only_validates_unique_identifier_code_constraints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->sut->validate('test', new NotBlank());
    }

    public function test_it_only_validates_a_string(): void
    {
        $this->repository->expects($this->never())->method('get')->with($this->anything());
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate(new \stdClass(), new UniqueIdentifierGeneratorCode());
    }

    public function test_it_does_not_add_a_violations_if_the_code_does_not_exist(): void
    {
        $this->repository->expects($this->once())->method('get')->with('new_identifier_code')->willThrowException(new CouldNotFindIdentifierGeneratorException('new_identifier_code'));
        $this->context->expects($this->never())->method('buildViolation');
        $this->sut->validate('new_identifier_code', new UniqueIdentifierGeneratorCode());
    }

    public function test_it_adds_a_a_violation_if_the_code_is_already_used(): void
    {
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new UniqueIdentifierGeneratorCode();
        $this->repository->expects($this->once())->method('get')->with('existing_code')->willReturn(new IdentifierGenerator(
            IdentifierGeneratorId::fromString('fdf0a55e-0337-4f2c-93f5-c2de84353ea2'),
            IdentifierGeneratorCode::fromString('existing_code'),
            Conditions::fromArray([new EmptyIdentifier('sku')]),
            Structure::fromArray([new AutoNumber(1, 4)]),
            LabelCollection::fromNormalized([]),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString(TextTransformation::NO)
        ));
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($violationsBuilder);
        $violationsBuilder->expects($this->once())->method('addViolation')->willReturn($violationsBuilder);
        $this->sut->validate('existing_code', $constraint);
    }
}
