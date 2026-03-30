<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUnique;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\CodeMustBeUniqueValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CodeMustBeUniqueValidatorTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private ExecutionContextInterface|MockObject $context;
    private CodeMustBeUniqueValidator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new CodeMustBeUniqueValidator($this->repository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(CodeMustBeUniqueValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_validates_a_connection_code_must_be_unique(): void
    {
        $constraint = new CodeMustBeUnique();
        $this->repository->method('findOneByCode')->with('magento')->willReturn(null);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->assertNull($this->sut->validate('magento', $constraint));
    }

    public function test_it_build_a_violation_if_the_code_is_not_unique(): void
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new CodeMustBeUnique();
        $this->repository->method('findOneByCode')->with('magento')->willReturn(new Connection(
            'magento',
            'Magento connector',
            FlowType::DATA_DESTINATION,
            42,
            50,
            null,
            true
        ));
        $this->context->expects($this->once())->method('buildViolation')->with('akeneo_connectivity.connection.connection.constraint.code.must_be_unique')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->assertNull($this->sut->validate('magento', $constraint));
    }
}
