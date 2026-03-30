<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExist;
use Akeneo\Connectivity\Connection\Application\Webhook\Validation\ConnectionMustExistValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ConnectionMustExistValidatorTest extends TestCase
{
    private ConnectionRepositoryInterface|MockObject $repository;
    private ExecutionContextInterface|MockObject $context;
    private ConnectionMustExistValidator $sut;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ConnectionRepositoryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new ConnectionMustExistValidator($this->repository);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(ConnectionMustExistValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_validates_that_a_connection_must_exist(): void
    {
        $constraint = new ConnectionMustExist();
        $magento = new Connection(
            'magento',
            'Magento connector',
            FlowType::DATA_DESTINATION,
            42,
            50,
            null,
            true
        );
        $this->repository->method('findOneByCode')->with('magento')->willReturn($magento);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('magento', $constraint);
    }

    public function test_it_build_a_violation_if_the_connection_does_not_exist(): void
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new ConnectionMustExist();
        $this->repository->method('findOneByCode')->with('magento')->willReturn(null);
        $this->context->expects($this->once())->method('buildViolation')->with('akeneo_connectivity.connection.webhook.error.not_found')->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('magento', $constraint);
    }
}
