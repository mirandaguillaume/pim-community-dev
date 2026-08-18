<?php

declare(strict_types=1);

namespace Akeneo\Test\UserManagement\Unit\ServiceApi;

use Akeneo\UserManagement\ServiceApi\ViolationsException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @copyright 2026 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViolationsExceptionTest extends TestCase
{
    public function test_it_is_a_logic_exception(): void
    {
        $exception = new ViolationsException(new ConstraintViolationList());

        $this->assertInstanceOf(\LogicException::class, $exception);
    }

    public function test_it_exposes_the_given_violations(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('a message', '', [], '', 'a_property', ''),
        ]);

        $exception = new ViolationsException($violations);

        $this->assertSame($violations, $exception->violations());
    }

    public function test_its_message_describes_the_constraint_violation_list(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('a message', '', [], '', 'a_property', ''),
        ]);

        $exception = new ViolationsException($violations);

        $this->assertStringContainsString('a message', $exception->getMessage());
    }

    public function test_it_falls_back_to_a_generic_message_for_a_non_standard_violation_list(): void
    {
        $violations = $this->createMock(ConstraintViolationListInterface::class);

        $exception = new ViolationsException($violations);

        $this->assertSame('Some violation(s) are raised', $exception->getMessage());
    }
}
