<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\Exception;

use Akeneo\Connectivity\Connection\Domain\Apps\Exception\InvalidAppAuthenticationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidAppAuthenticationExceptionTest extends TestCase
{
    private ConstraintViolationListInterface|MockObject $constraintViolationList;
    private InvalidAppAuthenticationException $sut;

    protected function setUp(): void
    {
        $this->constraintViolationList = $this->createMock(ConstraintViolationListInterface::class);
        $this->sut = new InvalidAppAuthenticationException($this->constraintViolationList);
    }

    public function test_it_is_initializable(): void
    {
        $this->assertInstanceOf(InvalidAppAuthenticationException::class, $this->sut);
    }

    public function test_it_returns_constraint_violation_list(): void
    {
        $this->constraintViolationList->method('count')->willReturn(1);
        $this->constraintViolationList->method('get')->with(0)->willReturn(new ConstraintViolation(
            'a_constraint_violation_message',
            '',
            [],
            '',
            'a_path',
            'invalid'
        ));
        $this->assertSame($this->constraintViolationList, $this->sut->getConstraintViolationList());
    }

    public function test_it_initializes_empty_message(): void
    {
        $this->constraintViolationList->method('count')->willReturn(0);
        $this->assertSame('', $this->sut->getMessage());
    }

    public function test_it_initializes_message(): void
    {
        $this->constraintViolationList->method('count')->willReturn(2);
        $this->constraintViolationList->method('get')->with(0)->willReturn(new ConstraintViolation(
            'a_constraint_violation_message',
            '',
            [],
            '',
            'a_path',
            'invalid'
        ));
        $this->constraintViolationList->method('get')->with(1)->willReturn(new ConstraintViolation(
            'another_constraint_violation_message',
            '',
            [],
            '',
            'a_path',
            'invalid'
        ));
        $this->sut = new InvalidAppAuthenticationException($this->constraintViolationList);
        $this->assertSame('a_constraint_violation_message', $this->sut->getMessage());
    }
}
