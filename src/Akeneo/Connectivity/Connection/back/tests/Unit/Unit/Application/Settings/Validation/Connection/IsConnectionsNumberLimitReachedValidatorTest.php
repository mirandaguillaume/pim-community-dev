<?php

declare(strict_types=1);

namespace Akeneo\Test\Unit\spec\Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Query\IsConnectionsNumberLimitReachedHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReached;
use Akeneo\Connectivity\Connection\Application\Settings\Validation\Connection\IsConnectionsNumberLimitReachedValidator;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\IsConnectionsNumberLimitReachedQueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionsNumberLimitReachedValidatorTest extends TestCase
{
    private IsConnectionsNumberLimitReachedQueryInterface|MockObject $isConnectionsNumberLimitReachedQuery;
    private ExecutionContextInterface|MockObject $context;
    private IsConnectionsNumberLimitReachedValidator $sut;

    protected function setUp(): void
    {
        $this->isConnectionsNumberLimitReachedQuery = $this->createMock(IsConnectionsNumberLimitReachedQueryInterface::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->sut = new IsConnectionsNumberLimitReachedValidator($this->isConnectionsNumberLimitReachedQuery);
        $this->sut->initialize($this->context);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(IsConnectionsNumberLimitReachedValidator::class, $this->sut);
    }

    public function test_it_is_a_constraint_validator(): void
    {
        $this->assertInstanceOf(ConstraintValidatorInterface::class, $this->sut);
    }

    public function test_it_throws_on_wrong_constraint_type(): void
    {
        $constraint = $this->createMock(Constraint::class);

        $this->expectException(UnexpectedTypeException::class);
        $this->sut->validate('test', $constraint);
    }

    public function test_it_validates_when_max_limit_is_not_reached(): void
    {
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(false);
        $this->context->expects($this->never())->method('buildViolation')->with($this->anything());
        $this->sut->validate('test', new IsConnectionsNumberLimitReached());
    }

    public function test_it_builds_violations_when_max_limit_is_reached(): void
    {
        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $constraint = new IsConnectionsNumberLimitReached();
        $this->isConnectionsNumberLimitReachedQuery->method('execute')->willReturn(true);
        $this->context->expects($this->once())->method('buildViolation')->with($constraint->message)->willReturn($builder);
        $builder->expects($this->once())->method('addViolation');
        $this->sut->validate('test', $constraint);
    }
}
